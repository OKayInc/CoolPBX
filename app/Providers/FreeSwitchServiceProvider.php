<?php

namespace App\Providers;

use App\Contracts\FreeSwitchConnectionManagerInterface;
use App\Services\FreeSwitch\FreeSwitchConnectionManager;
use App\Services\FreeSwitch\FreeSwitchResponseMerger;
use App\Services\FreeSwitch\FreeSwitchService;
use Illuminate\Support\ServiceProvider;

class FreeSwitchServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(
            FreeSwitchConnectionManagerInterface::class, 
            FreeSwitchConnectionManager::class
        );

        $this->app->singleton(FreeSwitchResponseMerger::class, function ($app) {
            return new FreeSwitchResponseMerger();
        });

        $this->app->singleton('freeswitch', function ($app) {
            return new FreeSwitchService(
                $app->make(FreeSwitchConnectionManagerInterface::class),
                $app->make(FreeSwitchResponseMerger::class)
            );
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}