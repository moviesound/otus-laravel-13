<?php

namespace Feature\Services\Bot\Scenario\Planning\Steps;

use App\Enums\Bot\StepResultType;
use App\Services\Bot\Scenario\Planning\Steps\AddPlanningDescriptionStep;
use App\Services\Bot\Scenario\Planning\Steps\AddPlanningTitleStep;
use App\Services\Bot\Scenario\Planning\Steps\PlanningDoneStep;
use App\Services\Bot\Scenario\Planning\Steps\SelectEventTypeStep;
use App\Services\Bot\Scenario\Planning\Steps\SelectTaskTypeStep;
use Tests\BotTestCase;
use Tests\Mocks\Bot\FakeMessenger;

class AddPlanningTitleStepTest extends BotTestCase
{
    public function test_user_can_add_title()
    {
        $context = $this->makeBotContext(
            message: 'Новая задача'
        );

        $result = app(AddPlanningTitleStep::class)
            ->handle($context);


        $this->assertEquals(
            StepResultType::SWITCH,
            $result->type
        );

        $this->assertEquals(
            AddPlanningDescriptionStep::STEP_KEY,
            $result->switchStep
        );

        $this->assertEquals(
            [
                'title' => 'Новая задача'
            ],
            $result->data
        );
    }


    public function test_long_title_returns_repeat()
    {
        $context = $this->makeBotContext(
            message: str_repeat('a', 251)
        );


        $result = app(AddPlanningTitleStep::class)
            ->handle($context);


        $this->assertEquals(
            StepResultType::REPEAT,
            $result->type
        );

        $this->assertNotEmpty(
            $result->error
        );
    }


    public function test_user_can_skip_title()
    {
        $context = $this->makeBotContext(
            message: 'continue'
        );


        $result = app(AddPlanningTitleStep::class)
            ->handle($context);


        $this->assertEquals(
            AddPlanningDescriptionStep::STEP_KEY,
            $result->switchStep
        );
    }


    public function test_back_returns_to_task_type_for_task()
    {
        $context = $this->makeBotContext(
            message: 'back',
            scenarioData: [
                'type' => 'task'
            ]
        );


        $result = app(AddPlanningTitleStep::class)
            ->handle($context);


        $this->assertEquals(
            SelectTaskTypeStep::STEP_KEY,
            $result->switchStep
        );
    }


    public function test_back_returns_to_event_type_for_event()
    {
        $context = $this->makeBotContext(
            message: 'back',
            scenarioData: [
                'type' => 'event'
            ]
        );


        $result = app(AddPlanningTitleStep::class)
            ->handle($context);


        $this->assertEquals(
            SelectEventTypeStep::STEP_KEY,
            $result->switchStep
        );
    }


    public function test_user_can_cancel_planning()
    {
        $context = $this->makeBotContext(
            message: 'cancel'
        );


        $result = app(AddPlanningTitleStep::class)
            ->handle($context);


        $this->assertEquals(
            PlanningDoneStep::STEP_KEY,
            $result->switchStep
        );
    }


    public function test_show_title_buttons()
    {
        $messenger = new FakeMessenger();


        $context = $this->makeBotContext(
            messenger: $messenger,
            scenarioData: [
                'type' => 'task'
            ]
        );


        app(AddPlanningTitleStep::class)
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


    public function test_show_contains_default_actions()
    {
        $messenger = new FakeMessenger();


        $context = $this->makeBotContext(
            messenger: $messenger,
            scenarioData: [
                'type' => 'task'
            ]
        );


        app(AddPlanningTitleStep::class)
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
}
