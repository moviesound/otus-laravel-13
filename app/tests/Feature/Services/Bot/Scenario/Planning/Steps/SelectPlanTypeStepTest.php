<?php

namespace Feature\Services\Bot\Scenario\Planning\Steps;

use App\Enums\Bot\StepResultType;
use App\Services\Bot\Scenario\Planning\Steps\SelectPlanTypeStep;
use Tests\BotTestCase;
use Tests\Mocks\Bot\FakeMessenger;

class SelectPlanTypeStepTest extends BotTestCase
{
    public function test_user_can_select_task_creation()
    {
        $context = $this->makeBotContext(
            message: 'addTaskBtn'
        );

        $step = app(SelectPlanTypeStep::class);

        $result = $step->handle($context);

        $this->assertEquals(
            'selectTaskType',
            $result->switchStep
        );

        $this->assertEquals(
            [
                'type' => 'task'
            ],
            $result->data
        );
    }

    public function test_user_can_select_event_creation()
    {
        $context = $this->makeBotContext(
            message: 'addEventBtn'
        );

        $step = app(SelectPlanTypeStep::class);

        $result = $step->handle($context);

        $this->assertEquals(
            'selectEventType',
            $result->switchStep
        );

        $this->assertEquals(
            [
                'type' => 'event'
            ],
            $result->data
        );
    }


    public function test_random_message_returns_repeat()
    {
        $context = $this->makeBotContext(
            message: 'some_random_text'
        );

        $step = app(SelectPlanTypeStep::class);

        $result = $step->handle($context);

        $this->assertEquals(
            StepResultType::REPEAT,
            $result->type
        );

        $this->assertNotEmpty(
            $result->error
        );
    }

    public function test_show_plan_type_buttons()
    {
        $messenger = new FakeMessenger();

        $context = $this->makeBotContext(
            messenger: $messenger
        );

        $step = app(SelectPlanTypeStep::class);

        $step->show($context);

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
