<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        using: function () {

            // WEB
            Route::middleware('web')
                ->domain(config('domains.web'))
                ->group(base_path('routes/web.php'));

            // API
            Route::middleware('api')
                ->domain(config('domains.api'))
                ->group(base_path('routes/api.php'));

            // ADMIN
            Route::middleware('web')
            //потом будет так: Route::middleware('admin')
                ->domain(config('domains.admin'))
                ->group(base_path('routes/admin.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
