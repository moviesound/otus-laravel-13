<?php

namespace Feature\Services\Bot\Scenario\Planning\Steps\Reminders;

use App\Enums\Bot\StepResultType;
use App\Services\Bot\Scenario\Planning\Steps\Reminders\AddRemindersStep;
use App\Services\Bot\Scenario\Planning\Steps\Reminders\AddReminderTextStep;
use App\Services\Bot\Scenario\Planning\Steps\Reminders\AskAnotherReminderStep;
use Tests\BotTestCase;
use Tests\Mocks\Bot\FakeMessenger;

class AddReminderTextStepTest extends BotTestCase
{
    public function test_user_can_add_reminder_text()
    {
        $context = $this->makeBotContext(
            message: 'important reminder',
            scenarioData: [
                'reminders' => [
                    [
                        'type' => 'days',
                        'value' => 2,
                    ],
                ],
            ]
        );


        $result = app(AddReminderTextStep::class)
            ->handle($context);


        $this->assertEquals(
            StepResultType::SWITCH,
            $result->type
        );


        $this->assertEquals(
            AskAnotherReminderStep::STEP_KEY,
            $result->switchStep
        );


        $this->assertEquals(
            [
                [
                    'type' => 'days',
                    'value' => 2,
                    'text' => 'important reminder',
                ],
            ],
            $result->data['reminders']
        );
    }


    public function test_without_reminders_returns_to_add_reminders()
    {
        $context = $this->makeBotContext(
            message: 'some text'
        );


        $result = app(AddReminderTextStep::class)
            ->handle($context);


        $this->assertEquals(
            AddRemindersStep::STEP_KEY,
            $result->switchStep
        );
    }


    public function test_user_can_skip_reminder_text()
    {
        $context = $this->makeBotContext(
            message: 'continue'
        );


        $result = app(AddReminderTextStep::class)
            ->handle($context);


        $this->assertEquals(
            AskAnotherReminderStep::STEP_KEY,
            $result->switchStep
        );
    }


    public function test_user_can_go_back_to_reminders()
    {
        $context = $this->makeBotContext(
            message: 'back'
        );


        $result = app(AddReminderTextStep::class)
            ->handle($context);


        $this->assertEquals(
            AddRemindersStep::STEP_KEY,
            $result->switchStep
        );
    }


    public function test_show_reminder_text_buttons()
    {
        $messenger = new FakeMessenger();


        $context = $this->makeBotContext(
            messenger: $messenger
        );


        app(AddReminderTextStep::class)
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
            'skip',
            $buttons
        );
    }
}
