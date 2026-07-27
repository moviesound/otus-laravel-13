<?php

namespace Feature\Services\Bot\Scenario\Planning\Steps\Tags;

use App\Enums\Bot\StepResultType;
use App\Services\Bot\Scenario\Planning\Steps\Tags\AddPlanningTagsStep;
use App\Services\Bot\Scenario\Planning\Steps\Tags\EditPlanningTagsStep;
use App\Services\Bot\Scenario\Planning\Steps\Tags\EditPlanningTagValueStep;
use Tests\BotTestCase;
use Tests\Mocks\Bot\FakeMessenger;

class EditPlanningTagValueStepTest extends BotTestCase
{
    public function test_user_can_edit_tag_value()
    {
        $context = $this->makeBotContext(
            message: 'important',
            scenarioData: [
                'tags' => [
                    'work',
                    'urgent',
                ],
                'editingTagIndex' => 1,
            ]
        );


        $result = app(EditPlanningTagValueStep::class)
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
                'tags' => [
                    'work',
                    'important',
                ],
            ],
            $result->data
        );
    }


    public function test_user_can_go_back_to_edit_tags()
    {
        $context = $this->makeBotContext(
            message: 'back',
            scenarioData: [
                'tags' => [
                    'work',
                ],
                'editingTagIndex' => 0,
            ]
        );


        $result = app(EditPlanningTagValueStep::class)
            ->handle($context);


        $this->assertEquals(
            StepResultType::SWITCH,
            $result->type
        );


        $this->assertEquals(
            EditPlanningTagsStep::STEP_KEY,
            $result->switchStep
        );
    }


    public function test_missing_editing_index_returns_to_edit_tags()
    {
        $context = $this->makeBotContext(
            message: 'new tag',
            scenarioData: [
                'tags' => [
                    'work',
                ],
            ]
        );


        $result = app(EditPlanningTagValueStep::class)
            ->handle($context);


        $this->assertEquals(
            EditPlanningTagsStep::STEP_KEY,
            $result->switchStep
        );
    }


    public function test_invalid_tag_index_returns_to_edit_tags()
    {
        $context = $this->makeBotContext(
            message: 'new tag',
            scenarioData: [
                'tags' => [
                    'work',
                ],
                'editingTagIndex' => 5,
            ]
        );


        $result = app(EditPlanningTagValueStep::class)
            ->handle($context);


        $this->assertEquals(
            EditPlanningTagsStep::STEP_KEY,
            $result->switchStep
        );
    }


    public function test_show_edit_tag_value()
    {
        $messenger = new FakeMessenger();


        $context = $this->makeBotContext(
            messenger: $messenger,
            scenarioData: [
                'tags' => [
                    'work',
                    'urgent',
                ],
                'editingTagIndex' => 1,
            ]
        );


        app(EditPlanningTagValueStep::class)
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
                'editingTagIndex' => 0,
            ]
        );


        app(EditPlanningTagValueStep::class)
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
