<?php

namespace Feature\Services\Bot\Scenario\Planning\Steps\RepeatingDates;

use App\Enums\Bot\RepeatType;
use App\Enums\Bot\StepResultType;
use App\Services\Bot\Scenario\Planning\Steps\PlanningDoneStep;
use App\Services\Bot\Scenario\Planning\Steps\RepeatingDates\Dates\SelectDateModeStep;
use App\Services\Bot\Scenario\Planning\Steps\RepeatingDates\Months\SelectMonthDaysStep;
use App\Services\Bot\Scenario\Planning\Steps\RepeatingDates\SelectRepeatingOrDateStep;
use App\Services\Bot\Scenario\Planning\Steps\RepeatingDates\SelectRepeatTypeStep;
use App\Services\Bot\Scenario\Planning\Steps\RepeatingDates\Weeks\SelectWeekDaysStep;
use Tests\BotTestCase;
use Tests\Mocks\Bot\FakeMessenger;

class SelectRepeatTypeStepTest extends BotTestCase
{
    public function test_user_can_select_daily_repeat()
    {
        $context = $this->makeBotContext(
            message: RepeatType::Daily->value
        );


        $result = app(SelectRepeatTypeStep::class)
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
            RepeatType::Daily->value,
            $result->data['repeat_type']
        );
    }

    public function test_user_can_select_weekly_repeat()
    {
        $context = $this->makeBotContext(
            message: RepeatType::Weekly->value
        );


        $result = app(SelectRepeatTypeStep::class)
            ->handle($context);


        $this->assertEquals(
            SelectWeekDaysStep::STEP_KEY,
            $result->switchStep
        );


        $this->assertEquals(
            RepeatType::Weekly->value,
            $result->data['repeat_type']
        );
    }

    public function test_user_can_select_monthly_repeat()
    {
        $context = $this->makeBotContext(
            message: RepeatType::Monthly->value
        );


        $result = app(SelectRepeatTypeStep::class)
            ->handle($context);


        $this->assertEquals(
            SelectMonthDaysStep::STEP_KEY,
            $result->switchStep
        );


        $this->assertEquals(
            RepeatType::Monthly->value,
            $result->data['repeat_type']
        );
    }


    public function test_invalid_repeat_type_returns_repeat()
    {
        $context = $this->makeBotContext(
            message: 'wrong'
        );


        $result = app(SelectRepeatTypeStep::class)
            ->handle($context);


        $this->assertEquals(
            StepResultType::REPEAT,
            $result->type
        );


        $this->assertNotEmpty(
            $result->error
        );
    }


    public function test_user_can_go_back()
    {
        $context = $this->makeBotContext(
            message: 'back'
        );


        $result = app(SelectRepeatTypeStep::class)
            ->handle($context);


        $this->assertEquals(
            SelectRepeatingOrDateStep::STEP_KEY,
            $result->switchStep
        );
    }


    public function test_user_can_cancel()
    {
        $context = $this->makeBotContext(
            message: 'cancel'
        );


        $result = app(SelectRepeatTypeStep::class)
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

        app(SelectRepeatTypeStep::class)
            ->show($context);

        $this->assertCount(
            1,
            $messenger->messages
        );

        $buttons = collect(
            $messenger->messages[0]['buttons']
        )
            ->flatten(1)
            ->pluck('callback_data')
            ->toArray();


        $this->assertContains(
            RepeatType::Daily->value,
            $buttons
        );

        $this->assertContains(
            RepeatType::Weekly->value,
            $buttons
        );

        $this->assertContains(
            RepeatType::Monthly->value,
            $buttons
        );

        $this->assertContains(
            RepeatType::Quarterly->value,
            $buttons
        );

        $this->assertContains(
            RepeatType::Yearly->value,
            $buttons
        );
    }

    public function test_show_contains_default_actions()
    {
        $messenger = new FakeMessenger();


        $context = $this->makeBotContext(
            messenger: $messenger
        );


        app(SelectRepeatTypeStep::class)
            ->show($context);


        $buttons = collect(
            $messenger->messages[0]['buttons']
        )
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
}
