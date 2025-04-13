<?php

namespace App\Services;

use App\Models\PullRequest;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Log;
use Telegram\Bot\Keyboard\Keyboard;
use Telegram\Bot\Laravel\Facades\Telegram;

class TelegramService
{
    public function sendMessage(array $params): void
    {
        try {
            Telegram::sendMessage($params);
        } catch (Exception $e) {
            Log::error('Error sending Telegram message:', [
                'error' => $e->getMessage(),
                'params' => $params,
            ]);
            throw $e;
        }
    }

    public function sendPullRequestToReviewers(PullRequest $pullRequest): void
    {
        $reviewers = User::where('team_id', $pullRequest->team_id)
            ->where('is_reviewer', true)
            ->where('id', '!=', $pullRequest->author_id)
            ->get();

        if ($reviewers->isEmpty()) {
            Log::warning('No reviewers found for team', [
                'team_id' => $pullRequest->team_id,
                'pull_request_id' => $pullRequest->id,
            ]);

            return;
        }

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

        foreach ($reviewers as $reviewer) {
            if ($reviewer->telegram_id) {
                try {
                    Log::info('Sending notification to reviewer', [
                        'reviewer_id' => $reviewer->id,
                        'telegram_id' => $reviewer->telegram_id,
                    ]);

                    Telegram::sendMessage([
                        'chat_id' => $reviewer->telegram_id,
                        'text' => __('telegram.reviewer.new_pr_notification', [
                            'author' => $pullRequest->author->name,
                            'url' => $pullRequest->url,
                        ]),
                        'reply_markup' => $keyboard,
                    ]);

                    Log::info('Notification sent successfully');
                } catch (Exception $e) {
                    Log::error('Error sending notification to reviewer', [
                        'reviewer_id' => $reviewer->id,
                        'telegram_id' => $reviewer->telegram_id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        }
    }

    public function notifyAuthorAboutReview(
        PullRequest $pullRequest,
        string $reviewerName,
        string $status,
        ?string $comment = null,
    ): void {
        if (! $pullRequest->author->telegram_id) {
            return;
        }

        $message = match ($status) {
            'approved' => __('telegram.reviewer.pr_approved_notification', [
                'url' => $pullRequest->url,
                'reviewer' => $reviewerName,
            ]),
            'changes_requested', 'returned' => __('telegram.reviewer.pr_changes_requested_notification', [
                'reviewer' => $reviewerName,
                'url' => $pullRequest->url,
                'comment' => $comment ? "\n\n{$comment}" : '',
            ]),
        };

        if ($status === 'approved') {
            if ($pullRequest->approvals_count < $pullRequest->required_approvals) {
                if ($pullRequest->required_approvals > 1) {
                    $message .= "\n\n".__('telegram.user.wait_second_approval');
                }
            } else {
                $message .= "\n\n".__('telegram.user.pr_approved');
                $this->notifyAllReviewersAboutApproval($pullRequest);
            }
        }

        $keyboard = null;
        if ($status === 'returned') {
            $keyboard = Keyboard::make()
                ->inline()
                ->row([
                    Keyboard::inlineButton([
                        'text' => '❓ Оспорить возврат',
                        'callback_data' => "dispute_{$pullRequest->id}",
                    ]),
                ])
                ->row([
                    Keyboard::inlineButton([
                        'text' => '✅ Я поправил',
                        'callback_data' => "fixed_{$pullRequest->id}",
                    ]),
                ]);
        }

        Telegram::sendMessage([
            'chat_id' => $pullRequest->author->telegram_id,
            'text' => $message,
            'reply_markup' => $keyboard,
        ]);
    }

    public function notifyReviewersAboutUpdate(PullRequest $pullRequest, string $type, ?string $comment = null): void
    {
        $approvedReviewerIds = $pullRequest->reviews()
            ->where('status', 'approved')
            ->pluck('reviewer_id')
            ->toArray();

        $activeReviewerIds = $pullRequest->reviews()
            ->whereIn('status', ['returned', 'changes_requested'])
            ->pluck('reviewer_id')
            ->toArray();

        $reviewers = User::where('team_id', $pullRequest->team_id)
            ->where('is_reviewer', true)
            ->whereIn('id', $activeReviewerIds)
            ->whereNotIn('id', $approvedReviewerIds)
            ->where('id', '!=', $pullRequest->author_id)
            ->get();

        foreach ($reviewers as $reviewer) {
            if ($reviewer->telegram_id) {
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

                Telegram::sendMessage([
                    'chat_id' => $reviewer->telegram_id,
                    'text' => __('telegram.reviewer.pr_updated', [
                        'author' => $pullRequest->author->name,
                        'url' => $pullRequest->url,
                        'comment' => $comment ? "\n\nКомментарий:\n{$comment}" : '',
                    ]),
                    'reply_markup' => $keyboard,
                ]);
            }
        }
    }

    public function requestReviewComment(int $chatId, int $pullRequestId): void
    {
        Telegram::sendMessage([
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

    protected function notifyAllReviewersAboutApproval(PullRequest $pullRequest): void
    {
        $reviewers = User::where('team_id', $pullRequest->team_id)
            ->where('is_reviewer', true)
            ->where('id', '!=', $pullRequest->author_id)
            ->get();

        foreach ($reviewers as $reviewer) {
            if ($reviewer->telegram_id) {
                Telegram::sendMessage([
                    'chat_id' => $reviewer->telegram_id,
                    'text' => __('telegram.reviewer.pr_fully_approved', [
                        'author' => $pullRequest->author->name,
                        'url' => $pullRequest->url,
                    ]),
                ]);
            }
        }
    }
}
