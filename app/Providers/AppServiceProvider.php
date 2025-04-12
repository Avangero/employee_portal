<?php

namespace App\Providers;

use App\Telegram\Commands\StartCommand;
use Illuminate\Support\ServiceProvider;
use Telegram\Bot\Api;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(Api::class, function () {
            $telegram = new Api(config('telegram.bot_token'));
            $telegram->addCommand(StartCommand::class);

            return $telegram;
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
