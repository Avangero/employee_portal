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
        $chatId = $this->getUpdate()->getMessage()->getChat()->getId();

        if ($this->authService->isAuthenticated($chatId)) {
            $this->replyWithMessage([
                'text' => "👋 С возвращением!\n\n".
                    'Отправьте мне ссылку на Pull Request, и я уведомлю всех ревьюверов в вашей команде.',
            ]);

            return;
        }

        $this->replyWithMessage([
            'text' => "👋 Добро пожаловать в бот для ревью Pull Request-ов!\n\n".
                "Для начала работы необходимо авторизоваться.\n".
                'Пожалуйста, введите ваш email:',
        ]);

        $this->authService->startAuth($chatId);
    }
}
