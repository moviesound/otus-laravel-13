<?php

namespace Feature\Services\Bot\Scenario\Planning\Steps;

use App\Enums\Bot\StepResultType;
use App\Services\Bot\Scenario\Planning\Steps\AddPlanningTitleStep;
use App\Services\Bot\Scenario\Planning\Steps\PlanningDoneStep;
use App\Services\Bot\Scenario\Planning\Steps\SelectEventTypeStep;
use App\Services\Bot\Scenario\Planning\Steps\SelectPlanTypeStep;
use Tests\BotTestCase;
use Tests\Mocks\Bot\FakeMessenger;

class SelectEventTypeStepTest extends BotTestCase
{
    public function test_user_can_select_event_type()
    {
        $eventType = config('steps.events')[0];

        $context = $this->makeBotContext(
            message: $eventType
        );

        $result = app(SelectEventTypeStep::class)
            ->handle($context);


        $this->assertEquals(
            StepResultType::SWITCH,
            $result->type
        );

        $this->assertEquals(
            AddPlanningTitleStep::STEP_KEY,
            $result->switchStep
        );

        $this->assertEquals(
            [
                'subType' => $eventType
            ],
            $result->data
        );
    }


    public function test_random_message_returns_repeat()
    {
        $context = $this->makeBotContext(
            message: 'some_random_text'
        );

        $result = app(SelectEventTypeStep::class)
            ->handle($context);


        $this->assertEquals(
            StepResultType::REPEAT,
            $result->type
        );

        $this->assertNotEmpty(
            $result->error
        );
    }


    public function test_user_can_go_back_to_plan_type_selection()
    {
        $context = $this->makeBotContext(
            message: 'back'
        );

        $result = app(SelectEventTypeStep::class)
            ->handle($context);


        $this->assertEquals(
            StepResultType::SWITCH,
            $result->type
        );

        $this->assertEquals(
            SelectPlanTypeStep::STEP_KEY,
            $result->switchStep
        );
    }


    public function test_user_can_cancel_planning()
    {
        $context = $this->makeBotContext(
            message: 'cancel'
        );

        $result = app(SelectEventTypeStep::class)
            ->handle($context);


        $this->assertEquals(
            StepResultType::SWITCH,
            $result->type
        );

        $this->assertEquals(
            PlanningDoneStep::STEP_KEY,
            $result->switchStep
        );
    }


    public function test_show_event_type_buttons()
    {
        $messenger = new FakeMessenger();

        $context = $this->makeBotContext(
            messenger: $messenger
        );

        app(SelectEventTypeStep::class)
            ->show($context);


        $this->assertCount(
            1,
            $messenger->messages
        );


        $message = $messenger->messages[0];


        $this->assertArrayHasKey(
            'buttons',
            $message
        );


        $this->assertNotEmpty(
            $message['buttons']
        );
    }


    public function test_show_contains_event_types_buttons()
    {
        $messenger = new FakeMessenger();

        $context = $this->makeBotContext(
            messenger: $messenger
        );

        $step = app(SelectEventTypeStep::class);

        $step->show($context);

        $buttons = collect($messenger->messages[0]['buttons'])
            ->flatten(1)
            ->pluck('callback_data')
            ->toArray();

        foreach (config('steps.events') as $eventType) {
            $this->assertContains(
                $eventType,
                $buttons
            );
        }
    }
}
