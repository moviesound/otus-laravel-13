<?php

namespace App\Providers;

use App\Services\Bot\Scenario\Planning\Steps\AddPlanningDescriptionStep;
use App\Services\Bot\Scenario\Planning\Steps\AddPlanningTitleStep;
use App\Services\Bot\Scenario\Planning\Steps\FindPlansStep;
use App\Services\Bot\Scenario\Planning\Steps\PlanningDoneStep;
use App\Services\Bot\Scenario\Planning\Steps\SelectEventTypeStep;
use App\Services\Bot\Scenario\Planning\Steps\SelectPlanTypeStep;
use App\Services\Bot\Scenario\Planning\Steps\SelectTaskTypeStep;
use App\Services\Bot\Scenario\Planning\Steps\Tags\AddPlanningTagsStep;
use App\Services\Bot\Scenario\StepRegistry;
use Illuminate\Support\ServiceProvider;

class BotProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->registerMessengers();
        $this->registerMiddleware();
        $this->registerScenarios();
        $this->registerRepositories();
        $this->tagBotSteps();
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }


    private function registerMessengers(): void
    {
        $this->app->bind(
            \App\Contracts\Bot\TelegramWebhook\TelegramGatewayInterface::class,
            \App\Services\Bot\TelegramWebhook\TelegramGateway::class
        );

        $this->app->bind(
            \App\Contracts\Bot\Messengers\MessageHandlerInterface::class,
            \App\Services\Bot\MessageHandler::class
        );

        $this->app->bind(\App\Contracts\Bot\Messengers\MessengerFactoryInterface::class,
            \App\Services\Bot\Messengers\MessengerFactoryDispatcher::class);

    }

    private function registerScenarios(): void
    {
        $this->app->bind(
            \App\Contracts\Bot\Scenario\ScenarioRouterInterface::class,
            \App\Services\Bot\Scenario\ScenarioRouter::class
        );

        $this->app->bind(
            \App\Contracts\Bot\Scenario\ScenarioResolverInterface::class,
            \App\Services\Bot\Scenario\ScenarioResolver::class
        );

        $this->app->bind(\App\Contracts\Bot\Scenario\StateManagerInterface::class,
            \App\Services\Bot\Scenario\StateManager::class);
    }

    private function registerMiddleware(): void
    {
        $this->app->bind(
            \App\Contracts\Bot\Scenario\Middleware\BotMiddlewareHandlerInterface::class,
            \App\Services\Bot\Scenario\Middleware\BotMiddlewarePipeline::class
        );

        $this->app->bind(
            \App\Contracts\Bot\Scenario\Middleware\Cases\PoliticsMiddlewareInterface::class,
            \App\Services\Bot\Scenario\Middleware\Cases\PoliticsMiddleware::class
        );
    }

    private function registerRepositories(): void
    {
        $this->app->bind(
            \App\Contracts\Bot\Repositories\StepRepositoryInterface::class,
            \App\Repositories\Bot\StepRepository::class
        );

        $this->app->bind(
            \App\Contracts\Bot\Repositories\UserRepositoryInterface::class,
            \App\Repositories\Bot\UserRepository::class
        );

        $this->app->bind(
            \App\Contracts\Bot\Repositories\TelegramRepositoryInterface::class,
            \App\Repositories\Bot\TelegramRepository::class
        );

        $this->app->bind(
            \App\Contracts\Bot\Repositories\PlanningRepository\CreationPlanningRepositoryInterface::class,
            \App\Repositories\Bot\PlanningRepository\CreationPlanningRepository::class
        );

        $this->app->bind(
            \App\Contracts\Bot\Repositories\PlanningRepository\UpdatingPlanningRepositoryInterface::class,
            \App\Repositories\Bot\PlanningRepository\UpdatingPlanningRepository::class
        );

        $this->app->bind(
            \App\Contracts\Bot\Repositories\PlanningRepository\DeletionPlanningRepositoryInterface::class,
            \App\Repositories\Bot\PlanningRepository\DeletionPlanningRepository::class
        );

        $this->app->bind(
            \App\Contracts\Bot\Repositories\PlanningRepository\ReadingPlanningRepositoryInterface::class,
            \App\Repositories\Bot\PlanningRepository\ReadingPlanningRepository::class
        );

        $this->app->bind(
            \App\Contracts\Bot\Repositories\PlanningRepository\SearchPlanningRepositoryInterface::class,
            \App\Repositories\Bot\PlanningRepository\SearchPlanningRepository::class
        );
    }

    private function tagBotSteps()
    {
        $this->app->singleton(
            StepRegistry::class,
            concrete: fn() => new StepRegistry([
                //Planning
                SelectPlanTypeStep::STEP_KEY => SelectPlanTypeStep::class,
                SelectTaskTypeStep::STEP_KEY => SelectTaskTypeStep::class,
                SelectEventTypeStep::STEP_KEY => SelectEventTypeStep::class,
                AddPlanningTitleStep::STEP_KEY => AddPlanningTitleStep::class,
                AddPlanningDescriptionStep::STEP_KEY => AddPlanningDescriptionStep::class,

                AddPlanningTagsStep::STEP_KEY => AddPlanningTagsStep::class,

                PlanningDoneStep::STEP_KEY => PlanningDoneStep::class,

                FindPlansStep::STEP_KEY => FindPlansStep::class,

                //onBoarding


                //Politics
            ])
        );
    }
}
