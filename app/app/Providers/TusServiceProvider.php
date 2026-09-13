<?php

namespace App\Providers;

use TusPhp\Tus\Server as TusServer;
use Illuminate\Support\ServiceProvider;

class TusServiceProvider extends ServiceProvider
{
    // ...

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('tus-server', function ($app) {
            $server = new TusServer();

            $server
                ->setApiPath('/api/tus')
                ->setUploadDir(storage_path('app/uploads'));
            return $server;
        });
    }

    // ...
}
