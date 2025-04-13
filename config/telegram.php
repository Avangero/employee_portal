<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Your Telegram Bot API Token
    |--------------------------------------------------------------------------
    |
    | To get a bot token, message @BotFather on Telegram.
    |
    */
    'bot_token' => env('TELEGRAM_BOT_TOKEN', ''),

    /*
    |--------------------------------------------------------------------------
    | Default Bot Name
    |--------------------------------------------------------------------------
    |
    | Here you may specify which of the bots you wish to use as
    | your default bot for regular use.
    |
    */
    'default' => 'common',

    /*
    |--------------------------------------------------------------------------
    | Bots
    |--------------------------------------------------------------------------
    |
    | Here you may configure various bots for use within your application.
    | Available settings:
    |
    | - token: Your Telegram Bot API Token
    | - username: Your bot username
    | - commands: Your bot commands
    |
    */
    'bots' => [
        'common' => [
            'token' => env('TELEGRAM_BOT_TOKEN', ''),
            'commands' => [
                App\Telegram\Commands\StartCommand::class,
            ],
        ],
    ],
];
