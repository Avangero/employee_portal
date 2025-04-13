<?php

namespace App\Http\Controllers;

use App\Models\PullRequest;
use App\Models\PullRequestReview;
use App\Models\User;
use App\Services\TelegramAuthService;
use App\Services\TelegramService;
use App\Telegram\Commands\StartCommand;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Telegram\Bot\Api;
use Telegram\Bot\BotsManager;
use Telegram\Bot\Keyboard\Keyboard;

class TelegramWebhookController extends Controller
{
    protected TelegramService $telegramService;

    protected TelegramAuthService $authService;

    protected Api $telegram;

    public function __construct(TelegramService $telegramService, TelegramAuthService $authService)
    {
        $this->telegramService = $telegramService;
        $this->authService = $authService;
        $this->telegram = app(BotsManager::class)->bot();

        // Регистрируем только StartCommand
        $this->telegram->addCommands([
            StartCommand::class,
        ]);
    }

    public function handle(Request $request)
    {
        try {
            $update = json_decode($request->getContent(), true);
            if (empty($update)) {
                Log::warning('Empty update received');

                return response()->json(['status' => 'error', 'message' => 'Empty update'], 400);
            }

            Log::info('Received Telegram update:', $update);

            if (isset($update['message'])) {
                return $this->handleMessage($update['message']);
            }

            if (isset($update['callback_query'])) {
                return $this->handleCallback($update['callback_query']);
            }

            return response()->json(['status' => 'ok']);
        } catch (Exception $e) {
            Log::error('Telegram webhook error: '.$e->getMessage(), [
                'exception' => $e,
                'request' => $request->all(),
            ]);

            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    protected function handleMessage(array $message): JsonResponse
    {
        $chatId = $message['chat']['id'];
        $text = $message['text'] ?? null;

        if (! $text) {
            return response()->json(['status' => 'error', 'message' => 'No text in message']);
        }

        // Проверяем, является ли сообщение командой /start
        if ($text === '/start') {
            $chatId = $message['chat']['id'];

            // Проверяем, авторизован ли пользователь
            if ($this->authService->isAuthenticated($chatId)) {
                $user = $this->getUser($chatId);
                $this->telegram->sendMessage([
                    'chat_id' => $chatId,
                    'text' => __('telegram.user.auth_success'),
                ]);

                return response()->json(['status' => 'ok']);
            }

            $this->telegram->sendMessage([
                'chat_id' => $chatId,
                'text' => '👋 Добро пожаловать в бот для ревью Pull Request\'ов!',
            ]);

            $this->telegram->sendMessage([
                'chat_id' => $chatId,
                'text' => 'Для начала работы, пожалуйста, авторизуйтесь, отправив ваш email.',
            ]);

            $this->authService->setState($chatId, TelegramAuthService::STATE_WAITING_EMAIL);

            return response()->json(['status' => 'ok']);
        }

        $user = $this->getUser($chatId);

        $pendingReview = cache()->get("pending_review_{$chatId}");
        if ($pendingReview) {
            try {
                $pullRequest = PullRequest::findOrFail($pendingReview);

                if ($pullRequest->approvals_count >= $pullRequest->required_approvals) {
                    cache()->forget("pending_review_{$chatId}");
                    $this->sendMessage($chatId, __('telegram.reviewer.pr_already_approved'));

                    return response()->json(['status' => 'error', 'message' => 'PR has all required approvals']);
                }

                PullRequestReview::create([
                    'pull_request_id' => $pullRequest->id,
                    'reviewer_id' => $user->id,
                    'status' => 'returned',
                    'comment' => $text,
                ]);

                $pullRequest->increment('returns_count');
                $pullRequest->updateStatus();

                cache()->forget("pending_review_{$chatId}");

                $this->sendMessage($chatId, __('telegram.reviewer.review_success'));

                $this->telegramService->notifyAuthorAboutReview($pullRequest, $user->name, 'returned', $text);

                return response()->json(['status' => 'success']);
            } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
                cache()->forget("pending_review_{$chatId}");
                $this->sendMessage($chatId, __('telegram.common.error'));

                return response()->json(['error' => 'PR not found'], 404);
            }
        }

        // Проверяем состояние авторизации
        $authState = $this->authService->getState($chatId);
        if ($authState) {
            return $this->handleAuthState($chatId, $text, $authState);
        }

        // Проверяем аутентификацию для остальных действий
        if (! $this->authService->isAuthenticated($chatId)) {
            $this->sendMessage($chatId, __('telegram.user.not_authenticated'));

            return response()->json(['status' => 'error', 'message' => 'Not authenticated']);
        }

        // Проверяем, ожидается ли комментарий для оспаривания
        $pendingDispute = cache()->get("pending_dispute_{$chatId}");
        if ($pendingDispute) {
            try {
                $pullRequest = PullRequest::findOrFail($pendingDispute);

                if (! $pullRequest->canBeReviewed()) {
                    $this->sendMessage($chatId, '❌ Этот Pull Request уже полностью одобрен и не может быть изменен.');
                    cache()->forget("pending_dispute_{$chatId}");

                    return response()->json(['status' => 'error', 'message' => 'PR is already approved']);
                }

                // Обработка оспаривания возврата
                if ($pullRequest->status === PullRequest::STATUS_RETURNED) {
                    $pullRequest->update([
                        'status' => PullRequest::STATUS_CREATED,
                        'returns_count' => $pullRequest->returns_count + 1,
                    ]);

                    // Получаем ревьювера, который вернул PR
                    $returnedByReviewer = $pullRequest->reviews()->where('status', 'returned')->latest()->first()
                        ->reviewer;

                    // Отправляем уведомление только ревьюверу, который вернул PR
                    if ($returnedByReviewer && $returnedByReviewer->telegram_id) {
                        $keyboard = Keyboard::make()
                            ->inline()
                            ->row([
                                Keyboard::inlineButton([
                                    'text' => '✅ Апрув',
                                    'callback_data' => "approve_{$pullRequest->id}",
                                ]),
                                Keyboard::inlineButton([
                                    'text' => '🔁 Вернуть на доработку',
                                    'callback_data' => "reject_{$pullRequest->id}",
                                ]),
                            ]);

                        $this->sendMessage($returnedByReviewer->telegram_id, "⚠️ {$pullRequest->author->name} оспорил ваш возврат Pull Request:\n\n".
                            ($text ? "Комментарий:\n{$text}\n\n" : '').
                            "Ссылка: {$pullRequest->url}\n\n".
                            'Пожалуйста, проверьте его снова.', $keyboard);
                    }

                    // Отправляем уведомление автору
                    $this->sendMessage($chatId, '✅ Ваше оспаривание принято. Ревьювер получит уведомление о необходимости повторной проверки.');

                    cache()->forget("pending_dispute_{$chatId}");

                    return response()->json(['status' => 'ok']);
                }

                $pullRequest->dispute();
                $this->telegramService->notifyReviewersAboutUpdate($pullRequest, 'disputed', $text);

                $this->sendMessage($chatId, '✅ Ваше объяснение отправлено.');

                cache()->forget("pending_dispute_{$chatId}");

                return response()->json(['status' => 'ok']);
            } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
                cache()->forget("pending_dispute_{$chatId}");
                $this->sendMessage($chatId, '❌ Извините, но этот Pull Request больше не существует или был удален.');

                return response()->json(['error' => 'PR not found'], 404);
            }
        }

        // Обработка URL Pull Request
        if (filter_var($text, FILTER_VALIDATE_URL)) {
            Log::info('Processing Pull Request URL:', ['url' => $text, 'user_id' => $user->id]);

            if (! $user->team_id) {
                Log::warning('User has no team:', ['user_id' => $user->id]);
                $this->sendMessage($chatId, '❌ Вы не состоите в команде. Пожалуйста, обратитесь к администратору.');

                return response()->json(['error' => 'User has no team']);
            }

            // Проверяем на дубликаты
            $existingPR = PullRequest::where('url', $text)
                ->where('team_id', $user->team_id)
                ->where('created_at', '>', now()->subDays(7))
                ->first();

            if ($existingPR) {
                Log::info('Duplicate Pull Request detected:', ['existing_pr_id' => $existingPR->id]);
                $this->sendMessage($chatId, '⚠️ Такой Pull Request уже был создан ранее.');

                return response()->json(['status' => 'error', 'message' => 'Duplicate PR']);
            }

            try {
                DB::beginTransaction();

                $pullRequest = PullRequest::create([
                    'url' => $text,
                    'author_id' => $user->id,
                    'team_id' => $user->team_id,
                    'status' => PullRequest::STATUS_CREATED,
                    'returns_count' => 0,
                    'approvals_count' => 0,
                    'required_approvals' => 0,
                ]);

                $pullRequest->updateRequiredApprovals();

                if ($pullRequest->required_approvals === 0) {
                    DB::rollBack();
                    $this->sendMessage($chatId, __('telegram.user.no_reviewers'));

                    return response()->json(['status' => 'error', 'message' => 'No reviewers available']);
                }

                DB::commit();

                Log::info('Pull Request created successfully:', [
                    'pr_id' => $pullRequest->id,
                    'required_approvals' => $pullRequest->required_approvals,
                ]);

                $this->sendMessage($chatId, __('telegram.user.pr_created'));

                $this->telegramService->sendPullRequestToReviewers($pullRequest);

                return response()->json(['status' => 'ok']);
            } catch (Exception $e) {
                DB::rollBack();
                Log::error('Failed to create Pull Request:', [
                    'error' => $e->getMessage(),
                    'url' => $text,
                    'user_id' => $user->id,
                ]);

                $this->sendMessage($chatId, __('telegram.common.error'));

                return response()->json(['error' => $e->getMessage()], 500);
            }
        }

        // Если сообщение не является командой или URL
        return response()->json(['status' => 'ok', 'message' => 'Message ignored']);
    }

    protected function handleAuthState(int $chatId, string $text, string $state): \Illuminate\Http\JsonResponse
    {
        $result = match ($state) {
            TelegramAuthService::STATE_WAITING_EMAIL => $this->authService->handleEmail($chatId, $text),
            TelegramAuthService::STATE_WAITING_PASSWORD => $this->authService->handlePassword($chatId, $text),
            default => ['success' => false, 'message' => 'Неизвестное состояние авторизации'],
        };

        $this->sendMessage($chatId, $result['message']);

        return response()->json(['status' => $result['success'] ? 'ok' : 'error']);
    }

    protected function handleCallback(array $callback): JsonResponse
    {
        $chatId = $callback['from']['id'];
        $data = $callback['data'];
        $messageId = $callback['message']['message_id'];

        try {
            if (! $this->authService->isAuthenticated($chatId)) {
                $this->telegram->editMessageText([
                    'chat_id' => $chatId,
                    'message_id' => $messageId,
                    'text' => __('telegram.user.not_authenticated'),
                ]);

                return response()->json(['status' => 'error', 'message' => 'Not authenticated']);
            }

            $user = $this->getUser($chatId);

            // Проверяем права только для действий ревьювера
            if ((str_starts_with($data, 'approve_') || str_starts_with($data, 'reject_')) && ! $user->is_reviewer) {
                $this->telegram->editMessageText([
                    'chat_id' => $chatId,
                    'message_id' => $messageId,
                    'text' => __('telegram.reviewer.not_reviewer'),
                ]);

                return response()->json(['status' => 'error', 'message' => 'Not a reviewer']);
            }

            if (str_starts_with($data, 'approve_')) {
                $pullRequestId = substr($data, 8);
                try {
                    $pullRequest = PullRequest::findOrFail($pullRequestId);

                    if ($pullRequest->approvals_count >= $pullRequest->required_approvals) {
                        $this->telegram->editMessageText([
                            'chat_id' => $chatId,
                            'message_id' => $messageId,
                            'text' => __('telegram.reviewer.pr_already_approved'),
                        ]);

                        return response()->json(['status' => 'error', 'message' => 'PR has all required approvals']);
                    }

                    if ($pullRequest->reviews()->where('reviewer_id', $user->id)->where('status', 'approved')->exists()) {
                        $this->telegram->editMessageText([
                            'chat_id' => $chatId,
                            'message_id' => $messageId,
                            'text' => __('telegram.reviewer.already_approved'),
                        ]);

                        return response()->json(['status' => 'error', 'message' => 'Already approved by this reviewer']);
                    }

                    PullRequestReview::create([
                        'pull_request_id' => $pullRequest->id,
                        'reviewer_id' => $user->id,
                        'status' => 'approved',
                    ]);

                    $pullRequest->updateStatus();
                    $pullRequest->updateRequiredApprovals();

                    $pullRequest->refresh();
                    if ($pullRequest->approvals_count >= $pullRequest->required_approvals) {
                        $this->telegram->editMessageText([
                            'chat_id' => $chatId,
                            'message_id' => $messageId,
                            'text' => __('telegram.reviewer.final_approval'),
                        ]);
                    } else {
                        $remainingApprovals = $pullRequest->required_approvals - $pullRequest->approvals_count;
                        $this->telegram->editMessageText([
                            'chat_id' => $chatId,
                            'message_id' => $messageId,
                            'text' => __('telegram.reviewer.approval_success', [
                                'count' => $remainingApprovals,
                                'word' => $this->getApprovalWord($remainingApprovals),
                            ]),
                        ]);
                    }

                    $this->telegramService->notifyAuthorAboutReview($pullRequest, $user->name, 'approved');

                    return response()->json(['status' => 'success']);
                } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
                    $this->telegram->editMessageText([
                        'chat_id' => $chatId,
                        'message_id' => $messageId,
                        'text' => __('telegram.common.pr_not_found'),
                    ]);

                    return response()->json(['error' => 'PR not found'], 404);
                }
            }

            if (str_starts_with($data, 'reject_')) {
                $pullRequestId = substr($data, 7);
                try {
                    $pullRequest = PullRequest::findOrFail($pullRequestId);

                    if ($pullRequest->approvals_count >= $pullRequest->required_approvals) {
                        $this->telegram->editMessageText([
                            'chat_id' => $chatId,
                            'message_id' => $messageId,
                            'text' => __('telegram.reviewer.pr_already_approved'),
                        ]);

                        return response()->json(['status' => 'error', 'message' => 'PR has all required approvals']);
                    }

                    cache()->put("pending_review_{$chatId}", $pullRequestId, now()->addMinutes(30));

                    $this->telegram->editMessageText([
                        'chat_id' => $chatId,
                        'message_id' => $messageId,
                        'text' => __('telegram.reviewer.request_comment'),
                    ]);

                    return response()->json(['status' => 'success']);
                } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
                    $this->telegram->editMessageText([
                        'chat_id' => $chatId,
                        'message_id' => $messageId,
                        'text' => __('telegram.common.pr_not_found'),
                    ]);

                    return response()->json(['error' => 'PR not found'], 404);
                }
            }

            if (str_starts_with($data, 'fixed_')) {
                $pullRequestId = substr($data, 6);
                try {
                    $pullRequest = PullRequest::findOrFail($pullRequestId);

                    // Проверяем, что это автор PR
                    if ($pullRequest->author_id !== $user->id) {
                        $this->telegram->editMessageText([
                            'chat_id' => $chatId,
                            'message_id' => $messageId,
                            'text' => '❌ Только автор Pull Request может отметить его как исправленный.',
                        ]);

                        return response()->json(['status' => 'error', 'message' => 'Not an author']);
                    }

                    // Проверяем только на полное одобрение
                    if ($pullRequest->approvals_count >= $pullRequest->required_approvals) {
                        $this->telegram->editMessageText([
                            'chat_id' => $chatId,
                            'message_id' => $messageId,
                            'text' => '❌ Этот Pull Request уже получил все необходимые апрувы.',
                        ]);

                        return response()->json(['status' => 'error', 'message' => 'PR has all required approvals']);
                    }

                    // Обновляем статус PR и уведомляем ревьюверов
                    $pullRequest->update(['status' => PullRequest::STATUS_CREATED]);
                    $this->telegramService->notifyReviewersAboutUpdate($pullRequest, 'fixed');

                    $this->telegram->editMessageText([
                        'chat_id' => $chatId,
                        'message_id' => $messageId,
                        'text' => '✅ Pull Request отмечен как исправленный. Ревьюверы уведомлены.',
                    ]);

                    return response()->json(['status' => 'success']);
                } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
                    $this->telegram->editMessageText([
                        'chat_id' => $chatId,
                        'message_id' => $messageId,
                        'text' => '❌ Извините, но этот Pull Request больше не существует или был удален.',
                    ]);

                    return response()->json(['error' => 'PR not found'], 404);
                }
            }

            if (str_starts_with($data, 'dispute_')) {
                $pullRequestId = substr($data, 8);
                try {
                    $pullRequest = PullRequest::findOrFail($pullRequestId);

                    // Проверяем, что это автор PR
                    if ($pullRequest->author_id !== $user->id) {
                        $this->telegram->editMessageText([
                            'chat_id' => $chatId,
                            'message_id' => $messageId,
                            'text' => '❌ Только автор Pull Request может оспорить замечания.',
                        ]);

                        return response()->json(['status' => 'error', 'message' => 'Not an author']);
                    }

                    // Проверяем только на полное одобрение
                    if ($pullRequest->approvals_count >= $pullRequest->required_approvals) {
                        $this->telegram->editMessageText([
                            'chat_id' => $chatId,
                            'message_id' => $messageId,
                            'text' => '❌ Этот Pull Request уже получил все необходимые апрувы.',
                        ]);

                        return response()->json(['status' => 'error', 'message' => 'PR has all required approvals']);
                    }

                    cache()->put("pending_dispute_{$chatId}", $pullRequestId, now()->addMinutes(30));

                    $this->telegram->editMessageText([
                        'chat_id' => $chatId,
                        'message_id' => $messageId,
                        'text' => '📝 Пожалуйста, объясните, почему вы не согласны с замечаниями.',
                    ]);

                    return response()->json(['status' => 'success']);
                } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
                    $this->telegram->editMessageText([
                        'chat_id' => $chatId,
                        'message_id' => $messageId,
                        'text' => '❌ Извините, но этот Pull Request больше не существует или был удален.',
                    ]);

                    return response()->json(['error' => 'PR not found'], 404);
                }
            }

            return response()->json(['status' => 'error', 'message' => 'Unknown callback data']);
        } catch (Exception $e) {
            Log::error('Error handling callback: '.$e->getMessage(), [
                'exception' => $e,
                'callback' => $callback,
            ]);

            try {
                $this->telegram->editMessageText([
                    'chat_id' => $chatId,
                    'message_id' => $messageId,
                    'text' => __('telegram.common.error'),
                ]);
            } catch (Exception $e) {
                Log::error('Error sending error message: '.$e->getMessage());
            }

            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    protected function getUser(int $chatId): ?User
    {
        return User::where('telegram_id', (string) $chatId)->first();
    }

    protected function getApprovalWord(int $count): string
    {
        return match ($count) {
            1 => 'апрув',
            2, 3, 4 => 'апрува',
            default => 'апрувов'
        };
    }

    private function sendMessage(int $chatId, string $text, array $buttons = []): void
    {
        $params = [
            'chat_id' => $chatId,
            'text' => $text,
            'parse_mode' => 'HTML',
        ];

        if (! empty($buttons)) {
            $params['reply_markup'] = json_encode([
                'inline_keyboard' => $buttons,
            ]);
        }

        $this->telegramService->sendMessage($params);
    }
}
