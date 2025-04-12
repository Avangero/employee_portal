<?php

return [
    'bot_token' => env('TELEGRAM_BOT_TOKEN'),
    'webhook_url' => env('TELEGRAM_WEBHOOK_URL'),
    'commands' => [
        App\Telegram\Commands\StartCommand::class,
        App\Telegram\Commands\HelpCommand::class,
        App\Telegram\Commands\RegisterCommand::class,
    ],
];
