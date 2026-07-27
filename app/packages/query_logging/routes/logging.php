<?php

use Illuminate\Support\Facades\Route;
use Packages\QueryLogging\Http\Controllers\ActionLogController;

$router = Route::middleware(config('query-logging.middleware_group', ['web']));

if (config('query-logging.domain')) {
    $router = $router->domain(config('query-logging.domain'));
}

$router->group(function () {
    Route::get('/logging', [
        \Packages\QueryLogging\Http\Controllers\ActionLogController::class,
        'index'
    ])
    ->name('query_logging::index')
    ->middleware(config('query-logging.route_middlewares', []));
});
