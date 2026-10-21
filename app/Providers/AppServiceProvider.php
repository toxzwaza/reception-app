<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // HTTPS接続を強制（ngrok等の開発環境用）
        if ($this->app->environment('local') && str_contains((string) config('app.url'), 'ngrok')) {
            URL::forceScheme('https');
        }
    }
}
