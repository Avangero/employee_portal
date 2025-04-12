<?php

namespace App\Services;

use App\Models\PullRequest;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Log;
use Telegram\Bot\Api;
use Telegram\Bot\Keyboard\Keyboard;

class TelegramService {
    protected Api $telegram;

    public function __construct() {
        $this->telegram = new Api(config('telegram.bot_token'));
    }

    public function sendPullRequestToReviewers(PullRequest $pullRequest): void {
        Log::info('Начинаем отправку PR ревьюверам', [
            'pull_request_id' => $pullRequest->id,
            'team_id' => $pullRequest->team_id,
            'author_id' => $pullRequest->author_id,
        ]);

        $pullRequest->updateRequiredApprovals();

        if ($pullRequest->required_approvals === 0) {
            Log::warning('В команде нет активных ревьюверов', [
                'team_id' => $pullRequest->team_id,
                'pull_request_id' => $pullRequest->id,
            ]);

            return;
        }

        $pullRequest->markAsInReview();

        $reviewers = User::where('team_id', $pullRequest->team_id)
            ->where('is_reviewer', true)
            ->where('id', '!=', $pullRequest->author_id)
            ->get();

        Log::info('Найдены ревьюверы', [
            'count' => $reviewers->count(),
            'reviewers' => $reviewers
                ->map(
                    fn($r) => [
                        'id' => $r->id,
                        'name' => $r->name,
                        'telegram_id' => $r->telegram_id,
                        'is_reviewer' => $r->is_reviewer,
                        'team_id' => $r->team_id,
                    ],
                )
                ->toArray(),
        ]);

        $keyboard = Keyboard::make()
            ->inline()
            ->row([
                Keyboard::inlineButton([
                    'text' => '✅ Апрувить',
                    'callback_data' => "approve_{$pullRequest->id}",
                ]),
                Keyboard::inlineButton([
                    'text' => '🔁 Вернуть на доработку',
                    'callback_data' => "reject_{$pullRequest->id}",
                ]),
            ]);

        foreach ($reviewers as $reviewer) {
            if ($reviewer->telegram_id) {
                try {
                    Log::info('Отправляем уведомление ревьюверу', [
                        'reviewer_id' => $reviewer->id,
                        'telegram_id' => $reviewer->telegram_id,
                    ]);

                    $this->telegram->sendMessage([
                        'chat_id' => $reviewer->telegram_id,
                        'text' => "🔍 Новый Pull Request на ревью\n\nАвтор: {$pullRequest->author->name}\nСсылка: {$pullRequest->url}",
                        'reply_markup' => $keyboard,
                    ]);

                    Log::info('Уведомление успешно отправлено');
                } catch (Exception $e) {
                    Log::error('Ошибка при отправке уведомления ревьюверу', [
                        'reviewer_id' => $reviewer->id,
                        'telegram_id' => $reviewer->telegram_id,
                        'error' => $e->getMessage(),
                    ]);
                }
            } else {
                Log::warning('У ревьювера нет telegram_id', [
                    'reviewer_id' => $reviewer->id,
                ]);
            }
        }
    }

    public function notifyAuthorAboutReview(
        PullRequest $pullRequest,
        string $reviewerName,
        string $status,
        ?string $comment = null,
    ): void {
        if (!$pullRequest->author->telegram_id) {
            return;
        }

        $message = match ($status) {
            'approved' => "✅ Ваш Pull Request принял {$reviewerName}.\n\nСсылка: {$pullRequest->url}",
            'changes_requested' => "🔁 {$reviewerName} запросил изменения" .
                ($comment ? ":\n\n{$comment}" : '') .
                "\n\nСсылка: {$pullRequest->url}",
            'returned' => "🔄 {$reviewerName} вернул Pull Request на доработку" .
                ($comment ? ":\n\n{$comment}" : '') .
                "\n\nСсылка: {$pullRequest->url}",
        };

        $keyboard = null;

        if ($status === 'approved') {
            if ($pullRequest->approvals_count < $pullRequest->required_approvals) {
                if ($pullRequest->required_approvals > 1) {
                    $message .= "\n\nОжидайте одобрения от других ревьюверов.";
                }
            } else {
                $message .= "\n\n🎉 Все необходимые ревьюверы одобрили ваш код. Можете мерджить!";

                // Отправляем уведомление всем ревьюверам о том, что PR полностью одобрен
                $this->notifyAllReviewersAboutApproval($pullRequest);
            }
        } elseif ($status === 'changes_requested' || $status === 'returned') {
            $keyboard = Keyboard::make()
                ->inline()
                ->row([
                    Keyboard::inlineButton([
                        'text' => '🛠 Я поправил',
                        'callback_data' => "fixed_{$pullRequest->id}",
                    ]),
                    Keyboard::inlineButton([
                        'text' => '❓ Оспорить',
                        'callback_data' => "dispute_{$pullRequest->id}",
                    ]),
                ]);
        }

        $this->telegram->sendMessage([
            'chat_id' => $pullRequest->author->telegram_id,
            'text' => $message,
            'reply_markup' => $keyboard,
        ]);
    }

    public function notifyReviewersAboutUpdate(PullRequest $pullRequest, string $type, ?string $comment = null): void {
        $message = match ($type) {
            'fixed' => "🔄 Автор внес изменения в Pull Request\nСсылка: {$pullRequest->url}",
            'disputed' => '❗️ Автор оспорил ваши замечания' .
                ($comment ? ":\n\n{$comment}" : '') .
                "\n\nСсылка: {$pullRequest->url}",
            'returned' => '🔄 Pull Request возвращен на доработку' .
                ($comment ? ":\n\n{$comment}" : '') .
                "\n\nСсылка: {$pullRequest->url}",
            'changes_requested' => '🔄 Запрошены изменения в Pull Request' .
                ($comment ? ":\n\n{$comment}" : '') .
                "\n\nСсылка: {$pullRequest->url}",
        };

        // Получаем ID ревьюверов с их последним статусом ревью
        $reviewStatuses = $pullRequest
            ->reviews()
            ->selectRaw('reviewer_id, MAX(id) as last_review_id')
            ->groupBy('reviewer_id')
            ->get()
            ->mapWithKeys(function ($review) use ($pullRequest) {
                $lastReview = $pullRequest->reviews()->where('id', $review->last_review_id)->first();

                return [$review->reviewer_id => $lastReview->status];
            });

        // Получаем ID тех, чей последний статус - approved
        $approvedReviewerIds = $reviewStatuses->filter(fn($status) => $status === 'approved')->keys()->toArray();

        // Получаем ID всех ревьюверов, которые уже оставляли комментарии
        $activeReviewerIds = $reviewStatuses->keys()->toArray();

        // Получаем только тех ревьюверов, которые:
        // 1. Уже участвовали в ревью (оставляли комментарии)
        // 2. Еще не апрувнули PR
        // 3. Не являются автором PR
        $reviewers = User::where('team_id', $pullRequest->team_id)
            ->where('is_reviewer', true)
            ->whereIn('id', $activeReviewerIds) // Только те, кто уже участвовал
            ->whereNotIn('id', $approvedReviewerIds) // Исключаем тех, кто уже апрувнул
            ->where('id', '!=', $pullRequest->author_id)
            ->get();

        Log::info('Отправляем уведомления ревьюверам об обновлении PR', [
            'pull_request_id' => $pullRequest->id,
            'approved_reviewers' => $approvedReviewerIds,
            'active_reviewers' => $activeReviewerIds,
            'notified_reviewers' => $reviewers->pluck('id')->toArray(),
            'type' => $type,
        ]);

        foreach ($reviewers as $reviewer) {
            if ($reviewer->telegram_id) {
                $keyboard = Keyboard::make()
                    ->inline()
                    ->row([
                        Keyboard::inlineButton([
                            'text' => '✅ Апрувить',
                            'callback_data' => "approve_{$pullRequest->id}",
                        ]),
                        Keyboard::inlineButton([
                            'text' => '🔁 Вернуть на доработку',
                            'callback_data' => "reject_{$pullRequest->id}",
                        ]),
                    ]);

                $this->telegram->sendMessage([
                    'chat_id' => $reviewer->telegram_id,
                    'text' => $message,
                    'reply_markup' => $keyboard,
                ]);
            }
        }
    }

    public function requestReviewComment(int $chatId, int $pullRequestId): void {
        $this->telegram->sendMessage([
            'chat_id' => $chatId,
            'text' => 'Пожалуйста, напишите комментарий к вашему решению:',
            'reply_markup' => Keyboard::make()
                ->inline()
                ->row([
                    Keyboard::inlineButton([
                        'text' => 'Отмена',
                        'callback_data' => "cancel_comment_{$pullRequestId}",
                    ]),
                ]),
        ]);
    }

    protected function notifyAllReviewersAboutApproval(PullRequest $pullRequest): void {
        $reviewers = User::where('team_id', $pullRequest->team_id)
            ->where('is_reviewer', true)
            ->where('id', '!=', $pullRequest->author_id)
            ->get();

        $message =
            "✅ Pull Request от {$pullRequest->author->name} полностью одобрен.\n" .
            "Разработчик проверяет на деве.\n\n" .
            "Ссылка: {$pullRequest->url}";

        foreach ($reviewers as $reviewer) {
            if ($reviewer->telegram_id) {
                $this->telegram->sendMessage([
                    'chat_id' => $reviewer->telegram_id,
                    'text' => $message,
                ]);
            }
        }
    }
}
