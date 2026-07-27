<?php

namespace Feature\Services\Bot\Scenario\Planning\Steps;

use App\Enums\Bot\StepResultType;
use App\Services\Bot\Scenario\Planning\Steps\AddPlanningDescriptionStep;
use App\Services\Bot\Scenario\Planning\Steps\AddPlanningTitleStep;
use App\Services\Bot\Scenario\Planning\Steps\PlanningDoneStep;
use App\Services\Bot\Scenario\Planning\Steps\Tags\AddPlanningTagsStep;
use Tests\BotTestCase;
use Tests\Mocks\Bot\FakeMessenger;

class AddPlanningDescriptionStepTest extends BotTestCase
{
    public function test_user_can_add_description()
    {
        $context = $this->makeBotContext(
            message: 'Моё описание задачи'
        );

        $result = app(AddPlanningDescriptionStep::class)
            ->handle($context);

        $this->assertEquals(
            StepResultType::SWITCH,
            $result->type
        );

        $this->assertEquals(
            AddPlanningTagsStep::STEP_KEY,
            $result->switchStep
        );

        $this->assertEquals(
            [
                'description' => 'Моё описание задачи'
            ],
            $result->data
        );
    }


    public function test_long_description_returns_repeat()
    {
        $context = $this->makeBotContext(
            message: str_repeat('a', 4001)
        );

        $result = app(AddPlanningDescriptionStep::class)
            ->handle($context);


        $this->assertEquals(
            StepResultType::REPEAT,
            $result->type
        );

        $this->assertNotEmpty(
            $result->error
        );
    }


    public function test_user_can_skip_description()
    {
        $context = $this->makeBotContext(
            message: 'continue'
        );

        $result = app(AddPlanningDescriptionStep::class)
            ->handle($context);


        $this->assertEquals(
            AddPlanningTagsStep::STEP_KEY,
            $result->switchStep
        );
    }


    public function test_user_can_go_back_to_title()
    {
        $context = $this->makeBotContext(
            message: 'back'
        );

        $result = app(AddPlanningDescriptionStep::class)
            ->handle($context);


        $this->assertEquals(
            AddPlanningTitleStep::STEP_KEY,
            $result->switchStep
        );
    }


    public function test_user_can_cancel_planning()
    {
        $context = $this->makeBotContext(
            message: 'cancel'
        );

        $result = app(AddPlanningDescriptionStep::class)
            ->handle($context);


        $this->assertEquals(
            PlanningDoneStep::STEP_KEY,
            $result->switchStep
        );
    }


    public function test_show_description_buttons()
    {
        $messenger = new FakeMessenger();

        $context = $this->makeBotContext(
            messenger: $messenger,
            scenarioData: [
                'type' => 'task',
            ]
        );


        app(AddPlanningDescriptionStep::class)
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


    public function test_show_contains_default_actions()
    {
        $messenger = new FakeMessenger();

        $context = $this->makeBotContext(
            messenger: $messenger,
            scenarioData: [
                'type' => 'task',
            ]
        );


        app(AddPlanningDescriptionStep::class)
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
