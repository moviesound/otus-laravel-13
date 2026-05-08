<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Relations\Relation;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(
            \App\Contracts\SysTextInterface::class,
            \App\Services\SysTextService::class
        );

        $this->app->bind(
            \App\Contracts\AdminInterface::class,
            \App\Services\AdminService::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Relation::morphMap(config('morph', []));
    }
}
