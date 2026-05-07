<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Auth\Access\AuthorizationException;
use Spatie\Permission\Exceptions\UnauthorizedException;
use Illuminate\Http\Request;

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
                ->group(base_path('routes/admin/main.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->redirectGuestsTo('/login');
        $middleware->alias([
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (Throwable $e, Request $request) {

            if (
                $e instanceof AuthorizationException ||
                $e instanceof UnauthorizedException
            ) {

                if ($request->expectsJson() || $request->ajax()) {
                    return response()->json([
                        'message' => 'У вас нет прав'
                    ], 403);
                }

                return response()->view('errors.403', [], 403);
            }

            return null;
        });

    })->create();
