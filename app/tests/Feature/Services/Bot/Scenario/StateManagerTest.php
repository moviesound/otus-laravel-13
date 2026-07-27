<?php

namespace Tests\Feature\Services\Bot\Scenario;

use App\Contracts\Bot\Repositories\StepRepositoryInterface;
use App\Services\Bot\Scenario\StateManager;
use Tests\BotTestCase;

class StateManagerTest extends BotTestCase
{
    public function test_goto_changes_state_and_saves()
    {
        $repository = $this->mock(
            StepRepositoryInterface::class
        );


        $repository
            ->shouldReceive('save')
            ->once()
            ->withArgs(function ($state) {

                return
                    $state->scenario === 'onPolitics'
                    &&
                    $state->step === 'confirm'
                    &&
                    $state->userSocialId === 1;
            });


        $manager = new StateManager(
            $repository
        );


        $context = $this->makeBotContext(
            message: 'hello'
        );


        $manager->goto(
            $context,
            'onPolitics',
            'confirm'
        );


        $this->assertEquals(
            'onPolitics',
            $context->scenarioDTO->scenario
        );


        $this->assertEquals(
            'confirm',
            $context->scenarioDTO->step
        );


        $this->assertEquals(
            'hello',
            $context->scenarioDTO->message
        );
    }


    public function test_goto_merges_existing_data_with_new_data()
    {
        $repository = $this->mock(
            StepRepositoryInterface::class
        );


        $repository
            ->shouldReceive('save')
            ->once();


        $manager = new StateManager(
            $repository
        );


        $context = $this->makeBotContext(
            scenarioData: [
                'title' => 'Task',
                'time' => '10:00',
            ]
        );


        $manager->goto(
            $context,
            'planning',
            'nextStep',
            [
                'date' => '2026-07-27',
            ]
        );


        $data = json_decode(
            $context->scenarioDTO->data,
            true
        );


        $this->assertEquals(
            [
                'title' => 'Task',
                'time' => '10:00',
                'date' => '2026-07-27',
            ],
            $data
        );
    }


    public function test_clear_removes_user_state()
    {
        $repository = $this->mock(
            StepRepositoryInterface::class
        );


        $repository
            ->shouldReceive('clear')
            ->once()
            ->with(1);


        $manager = new StateManager(
            $repository
        );


        $context = $this->makeBotContext();


        $manager->clear(
            $context
        );
    }
}
