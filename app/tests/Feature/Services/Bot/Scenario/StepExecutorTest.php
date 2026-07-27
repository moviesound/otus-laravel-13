<?php

namespace Tests\Feature\Services\Bot\Scenario;

use App\Contracts\Bot\Scenario\StateManagerInterface;
use App\Contracts\Bot\Scenario\StepRegistryInterface;
use App\Contracts\Bot\Scenario\Steps\StepInterface;
use App\DTO\Bot\Scenarios\StepResultDTO;
use App\Enums\Bot\StepResultType;
use App\Models\Bot\Step;
use App\Services\Bot\Scenario\StepExecutor;
use Tests\BotTestCase;
use Tests\Mocks\Bot\FakeMessenger;

class StepExecutorTest extends BotTestCase
{
    public function test_execute_finish_deletes_messages_and_state()
    {
        $registry = $this->mock(
            StepRegistryInterface::class
        );

        $stateManager = $this->mock(
            StateManagerInterface::class
        );


        $executor = new StepExecutor(
            $registry,
            $stateManager
        );


        $step = $this->mock(
            StepInterface::class
        );


        $step
            ->shouldReceive('handle')
            ->once()
            ->andReturn(
                new StepResultDTO(
                    type: StepResultType::FINISH
                )
            );


        $messenger = new FakeMessenger();

        $context = $this->makeBotContext(
            messenger: $messenger
        );

        Step::factory()->create([
            'user_social_id' => 1,
        ]);


        $executor->execute(
            $step,
            $context
        );


        $this->assertEmpty(
            $messenger->messages
        );


        $this->assertDatabaseMissing(
            'steps',
            [
                'user_social_id' => 1,
            ],
            'main'
        );
    }


    public function test_execute_repeat_shows_error()
    {
        $registry = $this->mock(
            StepRegistryInterface::class
        );


        $stateManager = $this->mock(
            StateManagerInterface::class
        );


        $executor = new StepExecutor(
            $registry,
            $stateManager
        );


        $step = $this->mock(
            StepInterface::class
        );


        $step
            ->shouldReceive('handle')
            ->once()
            ->andReturn(
                new StepResultDTO(
                    type: StepResultType::REPEAT,
                    error: 'error'
                )
            );


        $step
            ->shouldReceive('show')
            ->once()
            ->withArgs(function ($context, $error) {

                return $error === 'error';

            });


        $context = $this->makeBotContext();


        $executor->execute(
            $step,
            $context
        );
    }


    public function test_execute_switch_changes_state_and_shows_next_step()
    {
        $registry = $this->mock(
            StepRegistryInterface::class
        );


        $stateManager = $this->mock(
            StateManagerInterface::class
        );


        $nextStep = $this->mock(
            StepInterface::class
        );


        $registry
            ->shouldReceive('get')
            ->once()
            ->with('nextStep')
            ->andReturn($nextStep);


        $stateManager
            ->shouldReceive('goto')
            ->once()
            ->withArgs(function (
                $context,
                $scenario,
                $step,
                $data,
                $additionalInfo
            ) {

                return
                    $scenario === 'onPlanning'
                    &&
                    $step === 'nextStep';

            });


        $nextStep
            ->shouldReceive('show')
            ->once();


        $executor = new StepExecutor(
            $registry,
            $stateManager
        );


        $step = $this->mock(
            StepInterface::class
        );


        $step
            ->shouldReceive('handle')
            ->once()
            ->andReturn(
                new StepResultDTO(
                    type: StepResultType::SWITCH,
                    switchStep: 'nextStep',
                    data: [
                        'test' => 1,
                    ],
                    additionalInfo: 'info'
                )
            );


        $context = $this->makeBotContext();


        $executor->execute(
            $step,
            $context
        );
    }


    public function test_execute_switch_without_step_throws_exception()
    {
        $executor = new StepExecutor(
            $this->mock(StepRegistryInterface::class),
            $this->mock(StateManagerInterface::class),
        );


        $step = $this->mock(
            StepInterface::class
        );


        $step
            ->shouldReceive('handle')
            ->once()
            ->andReturn(
                new StepResultDTO(
                    type: StepResultType::SWITCH
                )
            );


        $this->expectException(
            \RuntimeException::class
        );


        $context = $this->makeBotContext();


        $executor->execute(
            $step,
            $context
        );
    }
}
