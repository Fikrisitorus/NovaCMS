<?php

namespace App\Providers;

use App\Services\NovaApiClient;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(NovaApiClient::class, function () {
            return new NovaApiClient(
                rtrim((string) config('services.novacms.url'), '/'),
                (int) config('services.novacms.timeout', 15),
            );
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
