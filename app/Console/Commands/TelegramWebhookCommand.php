<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Telegram\Bot\Api;

class TelegramWebhookCommand extends Command
{
    protected $signature = 'telegram:webhook {--remove : Удалить вебхук, вместо установки}';

    protected $description = 'Установка веб-хука для телеграма';

    public function handle()
    {
        $telegram = new Api(config('telegram.bot_token'));

        if ($this->option('remove')) {
            $telegram->removeWebhook();
            $this->info('Вебхук удален успешно!');

            return;
        }

        $url = config('telegram.webhook_url');

        if (! $url) {
            $this->error('TELEGRAM_WEBHOOK_URL не прописан в .env');

            return;
        }

        $telegram->setWebhook(['url' => $url]);
        $this->info("Вебхук установлен: {$url}");
    }
}
