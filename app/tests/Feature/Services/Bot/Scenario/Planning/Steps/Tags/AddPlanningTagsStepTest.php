<?php

namespace Feature\Services\Bot\Scenario\Planning\Steps\Tags;

use App\Enums\Bot\StepResultType;
use App\Services\Bot\Scenario\Planning\Steps\AddPlanningDescriptionStep;
use App\Services\Bot\Scenario\Planning\Steps\PlanningDoneStep;
use App\Services\Bot\Scenario\Planning\Steps\RepeatingDates\SelectRepeatingOrDateStep;
use App\Services\Bot\Scenario\Planning\Steps\Tags\AddPlanningTagsStep;
use App\Services\Bot\Scenario\Planning\Steps\Tags\DeletePlanningTagsStep;
use App\Services\Bot\Scenario\Planning\Steps\Tags\EditPlanningTagsStep;
use Tests\BotTestCase;
use Tests\Mocks\Bot\FakeMessenger;

class AddPlanningTagsStepTest extends BotTestCase
{
    public function test_user_can_add_tags()
    {
        $context = $this->makeBotContext(
            message: 'work, urgent, php'
        );

        $result = app(AddPlanningTagsStep::class)
            ->handle($context);


        $this->assertEquals(
            StepResultType::SWITCH,
            $result->type
        );


        $this->assertEquals(
            SelectRepeatingOrDateStep::STEP_KEY,
            $result->switchStep
        );


        $this->assertArrayHasKey(
            'tags',
            $result->data
        );


        $this->assertNotEmpty(
            $result->data['tags']
        );
    }


    public function test_user_can_continue_without_tags()
    {
        $context = $this->makeBotContext(
            message: 'continue'
        );

        $result = app(AddPlanningTagsStep::class)
            ->handle($context);


        $this->assertEquals(
            SelectRepeatingOrDateStep::STEP_KEY,
            $result->switchStep
        );
    }


    public function test_user_can_go_back_to_description()
    {
        $context = $this->makeBotContext(
            message: 'back'
        );


        $result = app(AddPlanningTagsStep::class)
            ->handle($context);


        $this->assertEquals(
            AddPlanningDescriptionStep::STEP_KEY,
            $result->switchStep
        );
    }


    public function test_user_can_cancel_planning()
    {
        $context = $this->makeBotContext(
            message: 'cancel'
        );


        $result = app(AddPlanningTagsStep::class)
            ->handle($context);


        $this->assertEquals(
            PlanningDoneStep::STEP_KEY,
            $result->switchStep
        );
    }


    public function test_user_can_edit_tags()
    {
        $context = $this->makeBotContext(
            message: 'edit'
        );


        $result = app(AddPlanningTagsStep::class)
            ->handle($context);


        $this->assertEquals(
            EditPlanningTagsStep::STEP_KEY,
            $result->switchStep
        );
    }


    public function test_user_can_delete_tags()
    {
        $context = $this->makeBotContext(
            message: 'delete'
        );


        $result = app(AddPlanningTagsStep::class)
            ->handle($context);


        $this->assertEquals(
            DeletePlanningTagsStep::STEP_KEY,
            $result->switchStep
        );
    }


    public function test_show_tags_buttons()
    {
        $messenger = new FakeMessenger();

        $context = $this->makeBotContext(
            messenger: $messenger,
            scenarioData: [
                'type' => 'task',
            ]
        );


        app(AddPlanningTagsStep::class)
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


    public function test_show_existing_tags_has_edit_delete_buttons()
    {
        $messenger = new FakeMessenger();

        $context = $this->makeBotContext(
            messenger: $messenger,
            scenarioData: [
                'type' => 'task',
                'tags' => [
                    'work',
                    'urgent',
                ],
            ]
        );


        app(AddPlanningTagsStep::class)
            ->show($context);


        $buttons = collect($messenger->messages[0]['buttons'])
            ->flatten(1)
            ->pluck('callback_data')
            ->toArray();


        $this->assertContains(
            'edit',
            $buttons
        );


        $this->assertContains(
            'delete',
            $buttons
        );
    }
}
