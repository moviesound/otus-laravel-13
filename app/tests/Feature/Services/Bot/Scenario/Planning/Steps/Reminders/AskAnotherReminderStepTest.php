<?php

namespace Feature\Services\Bot\Scenario\Planning\Steps\Reminders;

use App\Enums\Bot\StepResultType;
use App\Services\Bot\Scenario\Planning\Steps\AlmostDonePlanningAddingStep;
use App\Services\Bot\Scenario\Planning\Steps\PlanningDoneStep;
use App\Services\Bot\Scenario\Planning\Steps\Reminders\AddRemindersStep;
use App\Services\Bot\Scenario\Planning\Steps\Reminders\AskAnotherReminderStep;
use Tests\BotTestCase;
use Tests\Mocks\Bot\FakeMessenger;

class AskAnotherReminderStepTest extends BotTestCase
{
    public function test_user_can_add_another_reminder()
    {
        $context = $this->makeBotContext(
            message: 'reminder_yes'
        );


        $result = app(AskAnotherReminderStep::class)
            ->handle($context);


        $this->assertEquals(
            StepResultType::SWITCH,
            $result->type
        );


        $this->assertEquals(
            AddRemindersStep::STEP_KEY,
            $result->switchStep
        );
    }


    public function test_user_can_finish_adding_reminders()
    {
        $context = $this->makeBotContext(
            message: 'reminder_no'
        );


        $result = app(AskAnotherReminderStep::class)
            ->handle($context);


        $this->assertEquals(
            AlmostDonePlanningAddingStep::STEP_KEY,
            $result->switchStep
        );
    }


    public function test_user_can_cancel_planning()
    {
        $context = $this->makeBotContext(
            message: 'cancel'
        );


        $result = app(AskAnotherReminderStep::class)
            ->handle($context);


        $this->assertEquals(
            PlanningDoneStep::STEP_KEY,
            $result->switchStep
        );
    }


    public function test_random_message_returns_repeat()
    {
        $context = $this->makeBotContext(
            message: 'random'
        );


        $result = app(AskAnotherReminderStep::class)
            ->handle($context);


        $this->assertEquals(
            StepResultType::REPEAT,
            $result->type
        );


        $this->assertNotEmpty(
            $result->error
        );
    }


    public function test_show_contains_reminder_buttons()
    {
        $messenger = new FakeMessenger();


        $context = $this->makeBotContext(
            messenger: $messenger
        );


        app(AskAnotherReminderStep::class)
            ->show($context);


        $this->assertCount(
            1,
            $messenger->messages
        );


        $buttons = collect($messenger->messages[0]['buttons'])
            ->flatten(1)
            ->pluck('callback_data')
            ->toArray();


        $this->assertContains(
            'reminder_yes',
            $buttons
        );


        $this->assertContains(
            'reminder_no',
            $buttons
        );


        $this->assertContains(
            'cancel',
            $buttons
        );
    }
}
