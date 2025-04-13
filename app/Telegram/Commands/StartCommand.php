<?php

namespace App\Telegram\Commands;

use App\Services\TelegramAuthService;
use Telegram\Bot\Commands\Command;

class StartCommand extends Command
{
    protected string $name = 'start';

    protected string $description = 'Начать работу с ботом';

    protected TelegramAuthService $authService;

    public function __construct()
    {
        $this->authService = app(TelegramAuthService::class);
    }

    public function handle()
    {
        $update = json_decode(request()->getContent(), true);
        \Log::info('Update data:', ['update' => $update]);

        if (empty($update['message'])) {
            \Log::error('No message in update');

            return;
        }

        $message = $update['message'];
        if (! isset($message['chat']['id'])) {
            \Log::error('No chat id in message', ['message' => $message]);

            return;
        }

        $chatId = $message['chat']['id'];
        \Log::info('Chat ID:', ['chat_id' => $chatId]);

        $this->telegram->sendMessage([
            'chat_id' => $chatId,
            'text' => '👋 Добро пожаловать в бот для ревью Pull Request\'ов!',
        ]);

        $this->telegram->sendMessage([
            'chat_id' => $chatId,
            'text' => 'Для начала работы, пожалуйста, авторизуйтесь, отправив ваш email.',
        ]);

        $this->authService->setState($chatId, TelegramAuthService::STATE_WAITING_EMAIL);
    }
}
