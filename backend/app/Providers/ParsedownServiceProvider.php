<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Parsedown;

class ParsedownServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(Parsedown::class, function ($app) {
            $parsedown = new Parsedown();
            $parsedown->setSafeMode(true);
            return $parsedown;
        });
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
