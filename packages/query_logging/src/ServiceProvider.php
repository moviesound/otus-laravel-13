<?php
namespace Packages\QueryLogging;

use Illuminate\Contracts\Http\Kernel;
use Packages\QueryLogging\Contracts\ActionLoggerInterface;
use Packages\QueryLogging\Contracts\LastCreatedModelStoreInterface;
use Packages\QueryLogging\Http\Middleware\ActionLogMiddleware;
use Packages\QueryLogging\Services\ActionLogger;
use Illuminate\Database\Eloquent\Model;
use Packages\QueryLogging\Services\LastCreatedModelStore;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            ActionLoggerInterface::class,
            ActionLogger::class
        );

        $this->app->singleton(
            LastCreatedModelStoreInterface::class,
            LastCreatedModelStore::class
        );
    }

    public function boot(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/query-logging.php',
            'query-logging'
        );

        $kernel = $this->app->make(Kernel::class);
        $kernel->appendMiddlewareToGroup(config('query-logging.middleware_group', 'web'), ActionLogMiddleware::class);

        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        $this->loadRoutesFrom(__DIR__.'/../routes/logging.php');
        $this->loadViewsFrom(__DIR__ . '/../views', 'query_logging');
        $this->loadViewsFrom(__DIR__ . '/../views', 'query_logging');


        Model::created(function (Model $model) {
            $key = $model->getKey();
            if (!$key) {
                return;
            }

            app(LastCreatedModelStoreInterface::class)
                ->set($key);
        });

        $this->publishes([
            __DIR__ . '/../config/query-logging.php' => config_path('query-logging.php'),
        ], 'query_logging.config');

        $this->publishes([
            __DIR__ . '/../views' => resource_path('views/vendor/query_logging'),
        ], 'query_logging.views');
    }
}
