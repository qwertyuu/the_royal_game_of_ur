<?php

namespace App\Providers;

use App\Services\BotAvailabilityService;
use GuzzleHttp\Client;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(BotAvailabilityService::class, function ($app) {
            return new BotAvailabilityService($app->make(Client::class));
        });
    }
}
