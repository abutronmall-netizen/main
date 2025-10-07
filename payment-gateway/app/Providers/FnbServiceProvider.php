<?php

namespace App\Providers;

use App\Services\Fnb\FnbClient;
use App\Services\Fnb\HttpClientFactory;
use Illuminate\Support\ServiceProvider;

class FnbServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(HttpClientFactory::class, function () {
            return new HttpClientFactory(
                baseUri: config('fnb.base_uri'),
                oauthUri: config('fnb.oauth_uri'),
                clientId: config('fnb.client_id'),
                clientSecret: config('fnb.client_secret'),
                certPath: config('fnb.cert_path'),
                certKeyPath: config('fnb.cert_key_path'),
                timeout: config('fnb.timeout'),
                connectTimeout: config('fnb.connect_timeout'),
                retryAttempts: config('fnb.retry_attempts'),
                retryDelayMs: config('fnb.retry_delay_ms'),
            );
        });

        $this->app->singleton(FnbClient::class, function ($app) {
            return new FnbClient(
                httpFactory: $app->make(HttpClientFactory::class),
                webhookSecret: config('fnb.webhook_secret'),
                verificationStrategy: config('fnb.webhook_verification'),
            );
        });
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../../config/fnb.php' => config_path('fnb.php'),
        ], 'config');
    }
}
