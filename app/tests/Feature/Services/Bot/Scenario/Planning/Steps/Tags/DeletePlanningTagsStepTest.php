<?php

namespace Feature\Services\Bot\Scenario\Planning\Steps\Tags;

use App\Enums\Bot\StepResultType;
use App\Services\Bot\Scenario\Planning\Steps\Tags\AddPlanningTagsStep;
use App\Services\Bot\Scenario\Planning\Steps\Tags\DeletePlanningTagsStep;
use Tests\BotTestCase;
use Tests\Mocks\Bot\FakeMessenger;

class DeletePlanningTagsStepTest extends BotTestCase
{
    public function test_user_can_delete_tag()
    {
        $context = $this->makeBotContext(
            message: '2',
            scenarioData: [
                'type' => 'task',
                'tags' => [
                    'work',
                    'urgent',
                    'php',
                ],
            ]
        );


        $result = app(DeletePlanningTagsStep::class)
            ->handle($context);


        $this->assertEquals(
            StepResultType::SWITCH,
            $result->type
        );


        $this->assertEquals(
            DeletePlanningTagsStep::STEP_KEY,
            $result->switchStep
        );


        $this->assertEquals(
            [
                'work',
                'php',
            ],
            $result->data['tags']
        );
    }


    public function test_delete_last_tag_returns_to_tags_step()
    {
        $context = $this->makeBotContext(
            message: '1',
            scenarioData: [
                'type' => 'task',
                'tags' => [
                    'work',
                ],
            ]
        );


        $result = app(DeletePlanningTagsStep::class)
            ->handle($context);


        $this->assertEquals(
            AddPlanningTagsStep::STEP_KEY,
            $result->switchStep
        );


        $this->assertEquals(
            [],
            $result->data['tags']
        );
    }


    public function test_wrong_value_returns_repeat()
    {
        $context = $this->makeBotContext(
            message: 'abc',
            scenarioData: [
                'type' => 'task',
                'tags' => [
                    'work',
                ],
            ]
        );


        $result = app(DeletePlanningTagsStep::class)
            ->handle($context);


        $this->assertEquals(
            StepResultType::REPEAT,
            $result->type
        );


        $this->assertNotEmpty(
            $result->error
        );
    }


    public function test_unknown_tag_number_returns_repeat()
    {
        $context = $this->makeBotContext(
            message: '10',
            scenarioData: [
                'type' => 'task',
                'tags' => [
                    'work',
                    'php',
                ],
            ]
        );


        $result = app(DeletePlanningTagsStep::class)
            ->handle($context);


        $this->assertEquals(
            StepResultType::REPEAT,
            $result->type
        );
    }


    public function test_user_can_go_back_to_tags()
    {
        $context = $this->makeBotContext(
            message: 'back',
            scenarioData: [
                'type' => 'task',
                'tags' => [
                    'work',
                ],
            ]
        );


        $result = app(DeletePlanningTagsStep::class)
            ->handle($context);


        $this->assertEquals(
            AddPlanningTagsStep::STEP_KEY,
            $result->switchStep
        );
    }


    public function test_show_delete_tags_buttons()
    {
        $messenger = new FakeMessenger();


        $context = $this->makeBotContext(
            messenger: $messenger,
            scenarioData: [
                'type' => 'task',
                'tags' => [
                    'work',
                    'php',
                ],
            ]
        );


        app(DeletePlanningTagsStep::class)
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
}
