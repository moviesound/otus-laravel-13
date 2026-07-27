<?php

namespace Feature\Services\Bot\Scenario\Planning\Steps;

use App\Enums\Bot\StepResultType;
use App\Services\Bot\Scenario\Planning\Steps\AddPlanningTitleStep;
use App\Services\Bot\Scenario\Planning\Steps\PlanningDoneStep;
use App\Services\Bot\Scenario\Planning\Steps\SelectPlanTypeStep;
use App\Services\Bot\Scenario\Planning\Steps\SelectTaskTypeStep;
use Tests\BotTestCase;
use Tests\Mocks\Bot\FakeMessenger;

class SelectTaskTypeStepTest extends BotTestCase
{
    public function test_user_can_select_task_type()
    {
        $taskType = config('steps.tasks')[0];

        $context = $this->makeBotContext(
            message: $taskType
        );

        $step = app(SelectTaskTypeStep::class);

        $result = $step->handle($context);

        $this->assertEquals(
            AddPlanningTitleStep::STEP_KEY,
            $result->switchStep
        );

        $this->assertEquals(
            [
                'subType' => $taskType
            ],
            $result->data
        );
    }


    public function test_random_message_returns_repeat()
    {
        $context = $this->makeBotContext(
            message: 'some_random_text'
        );

        $step = app(SelectTaskTypeStep::class);

        $result = $step->handle($context);

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

        $step = app(SelectTaskTypeStep::class);

        $result = $step->handle($context);

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

        $step = app(SelectTaskTypeStep::class);

        $result = $step->handle($context);

        $this->assertEquals(
            PlanningDoneStep::STEP_KEY,
            $result->switchStep
        );
    }


    public function test_show_task_type_buttons()
    {
        $messenger = new FakeMessenger();

        $context = $this->makeBotContext(
            messenger: $messenger
        );

        $step = app(SelectTaskTypeStep::class);

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

    public function test_show_contains_task_types_buttons()
    {
        $messenger = new FakeMessenger();

        $context = $this->makeBotContext(
            messenger: $messenger
        );

        app(SelectTaskTypeStep::class)
            ->show($context);

        $buttons = collect($messenger->messages[0]['buttons'])
            ->flatten(1)
            ->pluck('callback_data')
            ->toArray();

        foreach (config('steps.tasks') as $taskType) {
            $this->assertContains(
                $taskType,
                $buttons
            );
        }
    }
}
