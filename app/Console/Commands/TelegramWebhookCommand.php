<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Telegram\Bot\Api;

class TelegramWebhookCommand extends Command
{
    protected $signature = 'telegram:webhook {--remove : Remove webhook instead of setting it}';

    protected $description = 'Set or remove Telegram webhook';

    public function handle()
    {
        $telegram = new Api(config('telegram.bot_token'));

        if ($this->option('remove')) {
            $telegram->removeWebhook();
            $this->info('Webhook removed successfully');

            return;
        }

        $url = config('telegram.webhook_url');

        if (! $url) {
            $this->error('TELEGRAM_WEBHOOK_URL not set in .env');

            return;
        }

        $telegram->setWebhook(['url' => $url]);
        $this->info("Webhook set to: {$url}");
    }
}
