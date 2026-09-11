<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        RateLimiter::for('chatbot', function (Request $request) {
            $limit = (int) config('chatbot.rate_limit_per_minute', 20);
            $key = $request->user()?->id ?: $request->ip();

            return Limit::perMinute($limit)->by($key);
        });

        if ($this->app->runningInConsole()) {
            $this->commands([
                \App\Console\Commands\GeocodeProperties::class,
                \App\Console\Commands\AdminReset::class,
                \App\Console\Commands\ExportDatabase::class,
                \App\Console\Commands\ImportDatabase::class,
            ]);
        }
    }
}
