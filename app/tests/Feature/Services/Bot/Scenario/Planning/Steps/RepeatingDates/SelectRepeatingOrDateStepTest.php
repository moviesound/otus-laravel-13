<?php

namespace Feature\Services\Bot\Scenario\Planning\Steps\RepeatingDates;

use App\Enums\Bot\StepResultType;
use App\Services\Bot\Scenario\Planning\Steps\PlanningDoneStep;
use App\Services\Bot\Scenario\Planning\Steps\Reminders\AddRemindersStep;
use App\Services\Bot\Scenario\Planning\Steps\RepeatingDates\Dates\SelectDateModeStep;
use App\Services\Bot\Scenario\Planning\Steps\RepeatingDates\SelectRepeatingOrDateStep;
use App\Services\Bot\Scenario\Planning\Steps\RepeatingDates\SelectRepeatTypeStep;
use App\Services\Bot\Scenario\Planning\Steps\Tags\AddPlanningTagsStep;
use Tests\BotTestCase;
use Tests\Mocks\Bot\FakeMessenger;

class SelectRepeatingOrDateStepTest extends BotTestCase
{
    public function test_user_can_select_no_repeat()
    {
        $context = $this->makeBotContext(
            message: 'no_repeat'
        );


        $result = app(SelectRepeatingOrDateStep::class)
            ->handle($context);


        $this->assertEquals(
            StepResultType::SWITCH,
            $result->type
        );


        $this->assertEquals(
            SelectDateModeStep::STEP_KEY,
            $result->switchStep
        );


        $this->assertEquals(
            'no_repeat',
            $result->data['repeating_type']
        );


        $this->assertEquals(
            'none',
            $result->data['repeat_type']
        );
    }


    public function test_user_can_select_repeat()
    {
        $context = $this->makeBotContext(
            message: 'repeat'
        );


        $result = app(SelectRepeatingOrDateStep::class)
            ->handle($context);


        $this->assertEquals(
            SelectRepeatTypeStep::STEP_KEY,
            $result->switchStep
        );


        $this->assertEquals(
            'repeat',
            $result->data['repeating_type']
        );
    }


    public function test_invalid_message_returns_repeat()
    {
        $context = $this->makeBotContext(
            message: 'wrong'
        );


        $result = app(SelectRepeatingOrDateStep::class)
            ->handle($context);


        $this->assertEquals(
            StepResultType::REPEAT,
            $result->type
        );


        $this->assertNotEmpty(
            $result->error
        );
    }


    public function test_user_can_go_back_to_tags()
    {
        $context = $this->makeBotContext(
            message: 'back'
        );


        $result = app(SelectRepeatingOrDateStep::class)
            ->handle($context);


        $this->assertEquals(
            AddPlanningTagsStep::STEP_KEY,
            $result->switchStep
        );
    }


    public function test_user_can_continue_to_reminders()
    {
        $context = $this->makeBotContext(
            message: 'continue'
        );


        $result = app(SelectRepeatingOrDateStep::class)
            ->handle($context);


        $this->assertEquals(
            AddRemindersStep::STEP_KEY,
            $result->switchStep
        );
    }


    public function test_user_can_cancel()
    {
        $context = $this->makeBotContext(
            message: 'cancel'
        );


        $result = app(SelectRepeatingOrDateStep::class)
            ->handle($context);


        $this->assertEquals(
            PlanningDoneStep::STEP_KEY,
            $result->switchStep
        );
    }


    public function test_show_contains_repeat_buttons()
    {
        $messenger = new FakeMessenger();


        $context = $this->makeBotContext(
            messenger: $messenger
        );


        app(SelectRepeatingOrDateStep::class)
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
            'repeat',
            $buttons
        );


        $this->assertContains(
            'no_repeat',
            $buttons
        );
    }


    public function test_show_contains_default_actions()
    {
        $messenger = new FakeMessenger();


        $context = $this->makeBotContext(
            messenger: $messenger
        );


        app(SelectRepeatingOrDateStep::class)
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
            'cancel',
            $buttons
        );
    }


    public function test_show_contains_skip_when_repeating_selected()
    {
        $messenger = new FakeMessenger();


        $context = $this->makeBotContext(
            messenger: $messenger,
            scenarioData: [
                'repeating_type' => 'repeat',
            ]
        );


        app(SelectRepeatingOrDateStep::class)
            ->show($context);


        $buttons = collect($messenger->messages[0]['buttons'])
            ->flatten(1)
            ->pluck('callback_data')
            ->toArray();


        $this->assertContains(
            'skip',
            $buttons
        );
    }
}
