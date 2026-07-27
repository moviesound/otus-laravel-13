<?php

namespace Feature\Services\Bot\Scenario\Planning\Steps\Reminders;

use App\Enums\Bot\StepResultType;
use App\Services\Bot\Scenario\Planning\Steps\PlanningDoneStep;
use App\Services\Bot\Scenario\Planning\Steps\Reminders\AddRemindersStep;
use App\Services\Bot\Scenario\Planning\Steps\Reminders\AddReminderTextStep;
use App\Services\Bot\Scenario\Planning\Steps\Reminders\SelectReminderValueStep;
use Tests\BotTestCase;
use Tests\Mocks\Bot\FakeMessenger;

class SelectReminderValueStepTest extends BotTestCase
{
    public function test_user_can_select_reminder_value()
    {
        $context = $this->makeBotContext(
            message: '5',
            additionalInfo: 'days'
        );


        $result = app(SelectReminderValueStep::class)
            ->handle($context);


        $this->assertEquals(
            StepResultType::SWITCH,
            $result->type
        );


        $this->assertEquals(
            AddReminderTextStep::STEP_KEY,
            $result->switchStep
        );


        $this->assertArrayHasKey(
            'reminders',
            $result->data
        );


        $this->assertEquals(
            [
                [
                    'type' => 'days',
                    'value' => 5,
                ]
            ],
            $result->data['reminders']
        );
    }


    public function test_user_can_add_second_reminder()
    {
        $context = $this->makeBotContext(
            message: '2',
            additionalInfo: 'weeks',
            scenarioData: [
                'reminders' => [
                    [
                        'type' => 'days',
                        'value' => 5,
                    ],
                ],
            ]
        );


        $result = app(SelectReminderValueStep::class)
            ->handle($context);


        $this->assertCount(
            2,
            $result->data['reminders']
        );


        $this->assertEquals(
            [
                'type' => 'weeks',
                'value' => 2,
            ],
            $result->data['reminders'][1]
        );
    }


    public function test_invalid_value_returns_repeat()
    {
        $context = $this->makeBotContext(
            message: 'abc',
            additionalInfo: 'days'
        );


        $result = app(SelectReminderValueStep::class)
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
            message: 'back',
            additionalInfo: 'days'
        );


        $result = app(SelectReminderValueStep::class)
            ->handle($context);


        $this->assertEquals(
            AddRemindersStep::STEP_KEY,
            $result->switchStep
        );
    }


    public function test_user_can_cancel()
    {
        $context = $this->makeBotContext(
            message: 'cancel',
            additionalInfo: 'days'
        );


        $result = app(SelectReminderValueStep::class)
            ->handle($context);


        $this->assertEquals(
            PlanningDoneStep::STEP_KEY,
            $result->switchStep
        );
    }


    public function test_show_contains_buttons()
    {
        $messenger = new FakeMessenger();


        $context = $this->makeBotContext(
            messenger: $messenger,
            additionalInfo: 'days'
        );


        app(SelectReminderValueStep::class)
            ->show($context);


        $this->assertCount(
            1,
            $messenger->messages
        );


        $this->assertArrayHasKey(
            'buttons',
            $messenger->messages[0]
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
