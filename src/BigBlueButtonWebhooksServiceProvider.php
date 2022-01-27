<?php

namespace BinaryCats\BigBlueButtonWebhooks;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class BigBlueButtonWebhooksServiceProvider extends ServiceProvider
{
    /**
     * Boot application services.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/bigbluebutton-webhooks.php' => config_path('bigbluebutton-webhooks.php'),
            ], 'config');
        }

        Route::macro('bigbluebuttonWebhooks', fn ($url) => Route::post($url, BigBlueButtonWebhooksController::class));
    }

    /**
     * Register application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/bigbluebutton-webhooks.php', 'bigbluebutton-webhooks');
    }
}
