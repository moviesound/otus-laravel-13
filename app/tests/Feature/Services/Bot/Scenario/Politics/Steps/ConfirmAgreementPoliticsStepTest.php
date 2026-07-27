<?php

namespace Tests\Feature\Services\Bot\Scenario\Politics\Steps;

use App\Enums\Bot\StepResultType;
use App\Models\Bot\User;
use App\Services\Bot\Scenario\Politics\Steps\ConfirmAgreementPoliticsStep;
use Tests\BotTestCase;
use Tests\Mocks\Bot\FakeMessenger;

class ConfirmAgreementPoliticsStepTest extends BotTestCase
{
    public function test_user_can_accept_politics()
    {
        $user = User::factory()->create([
            'politics_agreed' => 0,
        ]);

        $context = $this->makeBotContext(
            message: 'yes'
        );

        $context->userDTO->id = $user->id;


        $result = app(ConfirmAgreementPoliticsStep::class)
            ->handle($context);


        $this->assertEquals(
            StepResultType::FINISH,
            $result->type
        );


        $this->assertDatabaseHas(
            'users',
            [
                'id' => $user->id,
                'politics_agreed' => 1,
            ],
            'main'
        );
    }


    public function test_user_can_decline_politics()
    {
        $user = User::factory()->create([
            'politics_agreed' => 0,
        ]);

        $context = $this->makeBotContext(
            message: 'no'
        );

        $context->userDTO->id = $user->id;


        $result = app(ConfirmAgreementPoliticsStep::class)
            ->handle($context);


        $this->assertEquals(
            StepResultType::FINISH,
            $result->type
        );


        $this->assertDatabaseHas(
            'users',
            [
                'id' => $user->id,
                'politics_agreed' => 0,
            ],
            'main'
        );
    }


    public function test_invalid_message_returns_repeat()
    {
        $context = $this->makeBotContext(
            message: 'random'
        );


        $result = app(ConfirmAgreementPoliticsStep::class)
            ->handle($context);


        $this->assertEquals(
            StepResultType::REPEAT,
            $result->type
        );


        $this->assertNotEmpty(
            $result->error
        );
    }


    public function test_show_contains_yes_and_no_buttons()
    {
        $messenger = new FakeMessenger();

        $context = $this->makeBotContext(
            messenger: $messenger
        );


        app(ConfirmAgreementPoliticsStep::class)
            ->show($context);


        $this->assertCount(
            1,
            $messenger->messages
        );


        $buttons = collect(
            $messenger->messages[0]['buttons']
        )
            ->flatten(1)
            ->pluck('callback_data')
            ->toArray();


        $this->assertContains(
            'yes',
            $buttons
        );


        $this->assertContains(
            'no',
            $buttons
        );
    }


    public function test_user_can_resume_previous_scenario_after_accept()
    {
        $user = User::factory()->create([
            'politics_agreed' => 0,
        ]);

        $context = $this->makeBotContext(
            message: 'yes',
            scenarioData: [
                'resume' => [
                    'scenario' => 'onPlanning',
                    'step' => 'someStep',
                ],
            ]
        );

        $context->userDTO->id = $user->id;


        $result = app(ConfirmAgreementPoliticsStep::class)
            ->handle($context);


        $this->assertEquals(
            StepResultType::SWITCH,
            $result->type
        );


        $this->assertEquals(
            'someStep',
            $result->switchStep
        );
    }
}
