<?php

namespace Feature\Services\Bot\Scenario\Planning\Steps\Reminders;

use App\Enums\Bot\StepResultType;
use App\Services\Bot\Scenario\Planning\Steps\AlmostDonePlanningAddingStep;
use App\Services\Bot\Scenario\Planning\Steps\PlanningDoneStep;
use App\Services\Bot\Scenario\Planning\Steps\Reminders\AddRemindersStep;
use App\Services\Bot\Scenario\Planning\Steps\Reminders\DeleteReminderStep;
use App\Services\Bot\Scenario\Planning\Steps\Reminders\SelectReminderValueStep;
use App\Services\Bot\Scenario\Planning\Steps\RepeatingDates\SelectRepeatingOrDateStep;
use Tests\BotTestCase;
use Tests\Mocks\Bot\FakeMessenger;

class AddRemindersStepTest extends BotTestCase
{
    public function test_user_can_select_hour_reminders()
    {
        $context = $this->makeBotContext(
            message: 'reminder_hours'
        );


        $result = app(AddRemindersStep::class)
            ->handle($context);


        $this->assertEquals(
            StepResultType::SWITCH,
            $result->type
        );


        $this->assertEquals(
            SelectReminderValueStep::STEP_KEY,
            $result->switchStep
        );


        $this->assertEquals(
            'hours',
            $result->additionalInfo
        );
    }


    public function test_user_can_select_day_reminders()
    {
        $context = $this->makeBotContext(
            message: 'reminder_days'
        );


        $result = app(AddRemindersStep::class)
            ->handle($context);


        $this->assertEquals(
            SelectReminderValueStep::STEP_KEY,
            $result->switchStep
        );


        $this->assertEquals(
            'days',
            $result->additionalInfo
        );
    }


    public function test_user_can_select_week_reminders()
    {
        $context = $this->makeBotContext(
            message: 'reminder_weeks'
        );


        $result = app(AddRemindersStep::class)
            ->handle($context);


        $this->assertEquals(
            SelectReminderValueStep::STEP_KEY,
            $result->switchStep
        );


        $this->assertEquals(
            'weeks',
            $result->additionalInfo
        );
    }


    public function test_user_can_select_month_reminders()
    {
        $context = $this->makeBotContext(
            message: 'reminder_months'
        );


        $result = app(AddRemindersStep::class)
            ->handle($context);


        $this->assertEquals(
            SelectReminderValueStep::STEP_KEY,
            $result->switchStep
        );


        $this->assertEquals(
            'months',
            $result->additionalInfo
        );
    }


    public function test_user_can_delete_reminders()
    {
        $context = $this->makeBotContext(
            message: 'delete_reminders'
        );


        $result = app(AddRemindersStep::class)
            ->handle($context);


        $this->assertEquals(
            DeleteReminderStep::STEP_KEY,
            $result->switchStep
        );
    }


    public function test_random_message_returns_repeat()
    {
        $context = $this->makeBotContext(
            message: 'random'
        );


        $result = app(AddRemindersStep::class)
            ->handle($context);


        $this->assertEquals(
            StepResultType::REPEAT,
            $result->type
        );


        $this->assertNotEmpty(
            $result->error
        );
    }


    public function test_user_can_go_back_to_date_selection()
    {
        $context = $this->makeBotContext(
            message: 'back'
        );


        $result = app(AddRemindersStep::class)
            ->handle($context);


        $this->assertEquals(
            SelectRepeatingOrDateStep::STEP_KEY,
            $result->switchStep
        );
    }


    public function test_user_can_continue()
    {
        $context = $this->makeBotContext(
            message: 'continue'
        );


        $result = app(AddRemindersStep::class)
            ->handle($context);


        $this->assertEquals(
            AlmostDonePlanningAddingStep::STEP_KEY,
            $result->switchStep
        );
    }


    public function test_user_can_cancel()
    {
        $context = $this->makeBotContext(
            message: 'cancel'
        );


        $result = app(AddRemindersStep::class)
            ->handle($context);


        $this->assertEquals(
            PlanningDoneStep::STEP_KEY,
            $result->switchStep
        );
    }


    public function test_show_reminder_buttons()
    {
        $messenger = new FakeMessenger();


        $context = $this->makeBotContext(
            messenger: $messenger,
            scenarioData: [
                'type' => 'task',
                'reminders' => [
                    [
                        'type' => 'days',
                        'value' => 5,
                    ],
                ],
            ]
        );


        app(AddRemindersStep::class)
            ->show($context);


        $this->assertCount(
            1,
            $messenger->messages
        );


        $this->assertArrayHasKey(
            'buttons',
            $messenger->messages[0]
        );


        $this->assertNotEmpty(
            $messenger->messages[0]['buttons']
        );
    }


    public function test_show_with_reminders_contains_delete_button()
    {
        $messenger = new FakeMessenger();

        $context = $this->makeBotContext(
            messenger: $messenger,
            scenarioData: [
                'type' => 'task',
                'reminders' => [
                    [
                        'type' => 'days',
                        'value' => 2,
                    ],
                ],
            ]
        );

        app(AddRemindersStep::class)
            ->show($context);

        $buttons = collect($messenger->messages[0]['buttons'])
            ->flatten(1)
            ->pluck('callback_data')
            ->toArray();

        $this->assertContains(
            'delete_reminders',
            $buttons
        );
    }


    public function test_show_contains_default_actions()
    {
        $messenger = new FakeMessenger();


        $context = $this->makeBotContext(
            messenger: $messenger,
            scenarioData: [
                'type' => 'task',
            ]
        );


        app(AddRemindersStep::class)
            ->show($context);


        $buttons = collect($messenger->messages[0]['buttons'])
            ->flatten(1)
            ->pluck('callback_data')
            ->toArray();


        $this->assertContains(
            'back',
            $buttons
        );


        $this->assertContains(
            'skip',
            $buttons
        );


        $this->assertContains(
            'cancel',
            $buttons
        );
    }
}
