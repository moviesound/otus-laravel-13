<?php

namespace Feature\Services\Bot\Scenario\Planning\Steps\Tags;

use App\Enums\Bot\StepResultType;
use App\Services\Bot\Scenario\Planning\Steps\Tags\AddPlanningTagsStep;
use App\Services\Bot\Scenario\Planning\Steps\Tags\EditPlanningTagsStep;
use App\Services\Bot\Scenario\Planning\Steps\Tags\EditPlanningTagValueStep;
use Tests\BotTestCase;
use Tests\Mocks\Bot\FakeMessenger;

class EditPlanningTagsStepTest extends BotTestCase
{
    public function test_user_can_select_tag_for_edit()
    {
        $context = $this->makeBotContext(
            message: '2',
            scenarioData: [
                'tags' => [
                    'work',
                    'urgent',
                ],
            ]
        );


        $result = app(EditPlanningTagsStep::class)
            ->handle($context);


        $this->assertEquals(
            StepResultType::SWITCH,
            $result->type
        );


        $this->assertEquals(
            EditPlanningTagValueStep::STEP_KEY,
            $result->switchStep
        );


        $this->assertEquals(
            [
                'editingTagIndex' => 1,
            ],
            $result->data
        );
    }


    public function test_invalid_tag_number_returns_repeat()
    {
        $context = $this->makeBotContext(
            message: '10',
            scenarioData: [
                'tags' => [
                    'work',
                    'urgent',
                ],
            ]
        );


        $result = app(EditPlanningTagsStep::class)
            ->handle($context);


        $this->assertEquals(
            StepResultType::REPEAT,
            $result->type
        );


        $this->assertNotEmpty(
            $result->error
        );
    }


    public function test_text_message_returns_repeat()
    {
        $context = $this->makeBotContext(
            message: 'wrong',
            scenarioData: [
                'tags' => [
                    'work',
                ],
            ]
        );


        $result = app(EditPlanningTagsStep::class)
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
            message: 'back',
            scenarioData: [
                'tags' => [
                    'work',
                ],
            ]
        );


        $result = app(EditPlanningTagsStep::class)
            ->handle($context);


        $this->assertEquals(
            StepResultType::SWITCH,
            $result->type
        );


        $this->assertEquals(
            AddPlanningTagsStep::STEP_KEY,
            $result->switchStep
        );
    }


    public function test_show_edit_tags()
    {
        $messenger = new FakeMessenger();


        $context = $this->makeBotContext(
            messenger: $messenger,
            scenarioData: [
                'tags' => [
                    'work',
                    'urgent',
                ],
            ]
        );


        app(EditPlanningTagsStep::class)
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


    public function test_show_contains_back_button()
    {
        $messenger = new FakeMessenger();


        $context = $this->makeBotContext(
            messenger: $messenger,
            scenarioData: [
                'tags' => [
                    'work',
                ],
            ]
        );


        app(EditPlanningTagsStep::class)
            ->show($context);


        $buttons = collect($messenger->messages[0]['buttons'])
            ->flatten(1)
            ->pluck('callback_data')
            ->toArray();


        $this->assertContains(
            'back',
            $buttons
        );
    }
}
