<?php

namespace App\Providers;

use App\Services\GeocodingService;
use App\Services\OtpService;
use Illuminate\Support\ServiceProvider;
use GuzzleHttp\Client;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(OtpService::class, function ($app) {
            return new OtpService();
        });
        $this->app->bind(GeocodingService::class, function ($app) {
            return new GeocodingService(new Client());
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
