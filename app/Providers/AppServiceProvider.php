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
        if (env('APP_ENV') === 'local' && str_contains(env('APP_URL'), 'ngrok')) {
            URL::forceScheme('https');
        }
    }
}
