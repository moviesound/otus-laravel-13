<?php

namespace Feature\Services\Bot\Scenario\Planning\Steps\Reminders;

use App\Enums\Bot\StepResultType;
use App\Services\Bot\Scenario\Planning\Steps\PlanningDoneStep;
use App\Services\Bot\Scenario\Planning\Steps\Reminders\AddRemindersStep;
use App\Services\Bot\Scenario\Planning\Steps\Reminders\DeleteReminderStep;
use Tests\BotTestCase;
use Tests\Mocks\Bot\FakeMessenger;

class DeleteReminderStepTest extends BotTestCase
{
    public function test_user_can_delete_reminder()
    {
        $context = $this->makeBotContext(
            message: '1',
            scenarioData: [
                'reminders' => [
                    [
                        'type' => 'days',
                        'value' => 2,
                    ],
                    [
                        'type' => 'weeks',
                        'value' => 1,
                    ],
                ],
            ]
        );


        $result = app(DeleteReminderStep::class)
            ->handle($context);


        $this->assertEquals(
            StepResultType::SWITCH,
            $result->type
        );


        $this->assertEquals(
            DeleteReminderStep::STEP_KEY,
            $result->switchStep
        );


        $this->assertEquals(
            [
                [
                    'type' => 'weeks',
                    'value' => 1,
                ],
            ],
            $result->data['reminders']
        );
    }


    public function test_invalid_number_returns_repeat()
    {
        $context = $this->makeBotContext(
            message: '5',
            scenarioData: [
                'reminders' => [
                    [
                        'type' => 'days',
                        'value' => 2,
                    ],
                ],
            ]
        );


        $result = app(DeleteReminderStep::class)
            ->handle($context);


        $this->assertEquals(
            StepResultType::REPEAT,
            $result->type
        );


        $this->assertNotEmpty(
            $result->error
        );
    }


    public function test_text_returns_repeat()
    {
        $context = $this->makeBotContext(
            message: 'abc'
        );


        $result = app(DeleteReminderStep::class)
            ->handle($context);


        $this->assertEquals(
            StepResultType::REPEAT,
            $result->type
        );


        $this->assertNotEmpty(
            $result->error
        );
    }


    public function test_user_can_go_back_to_reminders()
    {
        $context = $this->makeBotContext(
            message: 'back'
        );


        $result = app(DeleteReminderStep::class)
            ->handle($context);


        $this->assertEquals(
            AddRemindersStep::STEP_KEY,
            $result->switchStep
        );
    }


    public function test_user_can_cancel()
    {
        $context = $this->makeBotContext(
            message: 'cancel'
        );


        $result = app(DeleteReminderStep::class)
            ->handle($context);


        $this->assertEquals(
            PlanningDoneStep::STEP_KEY,
            $result->switchStep
        );
    }


    public function test_show_contains_default_actions()
    {
        $messenger = new FakeMessenger();


        $context = $this->makeBotContext(
            messenger: $messenger,
            scenarioData: [
                'reminders' => [
                    [
                        'type' => 'days',
                        'value' => 2,
                    ],
                ],
            ]
        );


        app(DeleteReminderStep::class)
            ->show($context);


        $this->assertCount(
            1,
            $messenger->messages
        );


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
