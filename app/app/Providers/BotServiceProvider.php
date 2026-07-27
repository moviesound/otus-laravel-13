<?php

namespace App\Providers;

use App\Services\Bot\Scenario\Planning\Steps\AddPlanningDescriptionStep;
use App\Services\Bot\Scenario\Planning\Steps\AddPlanningTitleStep;
use App\Services\Bot\Scenario\Planning\Steps\AlmostDonePlanningAddingStep;
use App\Services\Bot\Scenario\Planning\Steps\FindPlansStep;
use App\Services\Bot\Scenario\Planning\Steps\PlanningDoneStep;
use App\Services\Bot\Scenario\Planning\Steps\Reminders\AddRemindersStep;
use App\Services\Bot\Scenario\Planning\Steps\Reminders\AddReminderTextStep;
use App\Services\Bot\Scenario\Planning\Steps\Reminders\AskAnotherReminderStep;
use App\Services\Bot\Scenario\Planning\Steps\Reminders\DeleteReminderStep;
use App\Services\Bot\Scenario\Planning\Steps\Reminders\SelectReminderValueStep;
use App\Services\Bot\Scenario\Planning\Steps\RepeatingDates\Dates\AddDeadlineStep;
use App\Services\Bot\Scenario\Planning\Steps\RepeatingDates\Dates\AddPeriodEndStep;
use App\Services\Bot\Scenario\Planning\Steps\RepeatingDates\Dates\AddPeriodStartStep;
use App\Services\Bot\Scenario\Planning\Steps\RepeatingDates\Dates\AddRepeatDaysIntervalStep;
use App\Services\Bot\Scenario\Planning\Steps\RepeatingDates\Dates\SelectDateModeStep;
use App\Services\Bot\Scenario\Planning\Steps\RepeatingDates\Months\AskMonthlyTimeAddStep;
use App\Services\Bot\Scenario\Planning\Steps\RepeatingDates\Months\SelectMonthDaysStep;
use App\Services\Bot\Scenario\Planning\Steps\RepeatingDates\Months\SetMonthlyCommonTimeStep;
use App\Services\Bot\Scenario\Planning\Steps\RepeatingDates\Months\SetMonthlyDifferentTimeStep;
use App\Services\Bot\Scenario\Planning\Steps\RepeatingDates\Quarters\SelectQuarterlyDeadlineMonthStep;
use App\Services\Bot\Scenario\Planning\Steps\RepeatingDates\Quarters\SelectQuarterlyPeriodEndMonthStep;
use App\Services\Bot\Scenario\Planning\Steps\RepeatingDates\Quarters\SelectQuarterlyPeriodStartMonthStep;
use App\Services\Bot\Scenario\Planning\Steps\RepeatingDates\Quarters\SelectQuarterlyTypeStep;
use App\Services\Bot\Scenario\Planning\Steps\RepeatingDates\Quarters\SetQuarterlyDeadlineDayStep;
use App\Services\Bot\Scenario\Planning\Steps\RepeatingDates\Quarters\SetQuarterlyPeriodEndDayStep;
use App\Services\Bot\Scenario\Planning\Steps\RepeatingDates\Quarters\SetQuarterlyPeriodStartDayStep;
use App\Services\Bot\Scenario\Planning\Steps\RepeatingDates\SelectRepeatingOrDateStep;
use App\Services\Bot\Scenario\Planning\Steps\RepeatingDates\SelectRepeatTypeStep;
use App\Services\Bot\Scenario\Planning\Steps\RepeatingDates\Weeks\AskWeeklyTimeAddStep;
use App\Services\Bot\Scenario\Planning\Steps\RepeatingDates\Weeks\SelectWeekDaysStep;
use App\Services\Bot\Scenario\Planning\Steps\RepeatingDates\Weeks\SetWeeklyCommonTimeStep;
use App\Services\Bot\Scenario\Planning\Steps\RepeatingDates\Weeks\SetWeeklyDifferentTimeStep;
use App\Services\Bot\Scenario\Planning\Steps\RepeatingDates\Years\SelectYearlyDeadlineMonthStep;
use App\Services\Bot\Scenario\Planning\Steps\RepeatingDates\Years\SelectYearlyPeriodEndMonthStep;
use App\Services\Bot\Scenario\Planning\Steps\RepeatingDates\Years\SelectYearlyPeriodStartMonthStep;
use App\Services\Bot\Scenario\Planning\Steps\RepeatingDates\Years\SelectYearlyTypeStep;
use App\Services\Bot\Scenario\Planning\Steps\RepeatingDates\Years\SetYearlyDeadlineDayStep;
use App\Services\Bot\Scenario\Planning\Steps\RepeatingDates\Years\SetYearlyPeriodEndDayStep;
use App\Services\Bot\Scenario\Planning\Steps\RepeatingDates\Years\SetYearlyPeriodStartDayStep;
use App\Services\Bot\Scenario\Planning\Steps\SelectEventTypeStep;
use App\Services\Bot\Scenario\Planning\Steps\SelectPlanTypeStep;
use App\Services\Bot\Scenario\Planning\Steps\SelectTaskTypeStep;
use App\Services\Bot\Scenario\Planning\Steps\Tags\AddPlanningTagsStep;
use App\Services\Bot\Scenario\Planning\Steps\Tags\DeletePlanningTagsStep;
use App\Services\Bot\Scenario\Planning\Steps\Tags\EditPlanningTagsStep;
use App\Services\Bot\Scenario\Planning\Steps\Tags\EditPlanningTagValueStep;
use App\Services\Bot\Scenario\Politics\Steps\ConfirmAgreementPoliticsStep;
use App\Services\Bot\Scenario\StepRegistry;
use Illuminate\Support\ServiceProvider;

class BotServiceProvider extends ServiceProvider
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
        $this->registerUsers();
        $this->setBotSteps();
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
            \App\Services\Bot\Messengers\TelegramWebhook\TelegramGateway::class
        );

        $this->app->bind(
            \App\Contracts\Bot\Messengers\MessageHandlerInterface::class,
            \App\Services\Bot\MessageHandler::class
        );

        $this->app->bind(
            \App\Contracts\Bot\Messengers\MessengerFactoryInterface::class,
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

        $this->app->bind(
            \App\Contracts\Bot\Scenario\StateManagerInterface::class,
            \App\Services\Bot\Scenario\StateManager::class);

        $this->app->bind(
            \App\Contracts\Bot\Scenario\StepRegistryInterface::class,
            \App\Services\Bot\Scenario\StepRegistry::class);

        $this->app->bind(
            \App\Contracts\Bot\Scenario\StepExecutorInterface::class,
            \App\Services\Bot\Scenario\StepExecutor::class);
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

    private function registerUsers(): void
    {
        $this->app->bind(
            \App\Contracts\Bot\Users\UserResolverInterface::class,
            \App\Services\Bot\Users\UserResolverService::class
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

    private function setBotSteps()
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
                EditPlanningTagsStep::STEP_KEY => EditPlanningTagsStep::class,
                EditPlanningTagValueStep::STEP_KEY => EditPlanningTagValueStep::class,
                DeletePlanningTagsStep::STEP_KEY => DeletePlanningTagsStep::class,

                SelectRepeatingOrDateStep::STEP_KEY => SelectRepeatingOrDateStep::class,
                SelectRepeatTypeStep::STEP_KEY => SelectRepeatTypeStep::class,

                AddDeadlineStep::STEP_KEY => AddDeadlineStep::class,
                AddPeriodStartStep::STEP_KEY => AddPeriodStartStep::class,
                AddPeriodEndStep::STEP_KEY => AddPeriodEndStep::class,
                AddRepeatDaysIntervalStep::STEP_KEY => AddRepeatDaysIntervalStep::class,
                SelectDateModeStep::STEP_KEY => SelectDateModeStep::class,

                AskMonthlyTimeAddStep::STEP_KEY => AskMonthlyTimeAddStep::class,
                SelectMonthDaysStep::STEP_KEY => SelectMonthDaysStep::class,
                SetMonthlyCommonTimeStep::STEP_KEY => SetMonthlyCommonTimeStep::class,
                SetMonthlyDifferentTimeStep::STEP_KEY => SetMonthlyDifferentTimeStep::class,

                SelectQuarterlyDeadlineMonthStep::STEP_KEY => SelectQuarterlyDeadlineMonthStep::class,
                SelectQuarterlyPeriodStartMonthStep::STEP_KEY => SelectQuarterlyPeriodStartMonthStep::class,
                SelectQuarterlyPeriodEndMonthStep::STEP_KEY => SelectQuarterlyPeriodEndMonthStep::class,
                SelectQuarterlyTypeStep::STEP_KEY => SelectQuarterlyTypeStep::class,
                SetQuarterlyDeadlineDayStep::STEP_KEY => SetQuarterlyDeadlineDayStep::class,
                SetQuarterlyPeriodStartDayStep::STEP_KEY => SetQuarterlyPeriodStartDayStep::class,
                SetQuarterlyPeriodEndDayStep::STEP_KEY => SetQuarterlyPeriodEndDayStep::class,

                AskWeeklyTimeAddStep::STEP_KEY => AskWeeklyTimeAddStep::class,
                SelectWeekDaysStep::STEP_KEY => SelectWeekDaysStep::class,
                SetWeeklyCommonTimeStep::STEP_KEY => SetWeeklyCommonTimeStep::class,
                SetWeeklyDifferentTimeStep::STEP_KEY => SetWeeklyDifferentTimeStep::class,

                SelectYearlyDeadlineMonthStep::STEP_KEY => SelectYearlyDeadlineMonthStep::class,
                SelectYearlyPeriodStartMonthStep::STEP_KEY => SelectYearlyPeriodStartMonthStep::class,
                SelectYearlyPeriodEndMonthStep::STEP_KEY => SelectYearlyPeriodEndMonthStep::class,
                SelectYearlyTypeStep::STEP_KEY => SelectYearlyTypeStep::class,
                SetYearlyDeadlineDayStep::STEP_KEY => SetYearlyDeadlineDayStep::class,
                SetYearlyPeriodStartDayStep::STEP_KEY => SetYearlyPeriodStartDayStep::class,
                SetYearlyPeriodEndDayStep::STEP_KEY => SetYearlyPeriodEndDayStep::class,

                AddRemindersStep::STEP_KEY => AddRemindersStep::class,
                AddReminderTextStep::STEP_KEY => AddReminderTextStep::class,
                AskAnotherReminderStep::STEP_KEY => AskAnotherReminderStep::class,
                DeleteReminderStep::STEP_KEY => DeleteReminderStep::class,
                SelectReminderValueStep::STEP_KEY => SelectReminderValueStep::class,

                AlmostDonePlanningAddingStep::STEP_KEY => AlmostDonePlanningAddingStep::class,
                PlanningDoneStep::STEP_KEY => PlanningDoneStep::class,

                //FindPlansStep::STEP_KEY => FindPlansStep::class,
                FindPlansStep::STEP_KEY => FindPlansStep::class,

                //onBoarding


                //Politics
                ConfirmAgreementPoliticsStep::STEP_KEY => ConfirmAgreementPoliticsStep::class,
            ])
        );
    }
}
