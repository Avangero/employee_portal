<?php

namespace App\Console\Commands;

use App\Http\Controllers\TelegramWebhookController;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Http\Request;
use Telegram\Bot\Api;
use Telegram\Bot\Objects\Update;

class TelegramLongPollCommand extends Command {
    protected $signature = 'telegram:long-poll';
    protected $description = 'Start long polling for Telegram updates';

    protected TelegramWebhookController $controller;
    protected Api $telegram;
    protected int $offset = -1;

    public function __construct(TelegramWebhookController $controller) {
        parent::__construct();
        $this->controller = $controller;
        $this->telegram = new Api(config('telegram.bot_token'));
    }

    public function handle() {
        $this->info('Starting Telegram long polling...');

        while (true) {
            try {
                $updates = $this->telegram->getUpdates([
                    'offset' => $this->offset + 1,
                    'timeout' => 30,
                ]);

                if (empty($updates)) {
                    continue;
                }

                foreach ($updates as $update) {
                    $updateId = $update->get('update_id');
                    if (!$updateId) {
                        $this->error('Update ID is missing');

                        continue;
                    }

                    $this->info("Processing update {$updateId}");

                    try {
                        // Создаем новый Request с данными update
                        $request = Request::create(
                            'api/telegram/webhook',
                            'POST',
                            [],
                            [],
                            [],
                            [],
                            json_encode($update->toArray()),
                        );
                        $request->headers->set('Content-Type', 'application/json');

                        // Обрабатываем update через контроллер
                        $this->controller->handle($request);

                        // Обновляем offset только после успешной обработки
                        $this->offset = $updateId;
                        $this->info("Successfully processed update {$updateId}");
                    } catch (Exception $e) {
                        $this->error("Failed to process update {$updateId}: {$e->getMessage()}");
                        // Даже если обработка не удалась, обновляем offset чтобы не застрять
                        $this->offset = $updateId;
                    }
                }
            } catch (Exception $e) {
                $this->error("Error in polling loop: {$e->getMessage()}");
                sleep(1);
            }
        }
    }
}
