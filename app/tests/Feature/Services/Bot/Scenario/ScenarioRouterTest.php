<?php

namespace Tests\Feature\Services\Bot\Scenario;

use App\Contracts\Bot\Scenario\StepExecutorInterface;
use App\Contracts\Bot\Scenario\Steps\StepInterface;
use App\Contracts\SysTextInterface;
use App\Services\Bot\Contexts\BotContext;
use App\Services\Bot\Scenario\ScenarioRouter;
use App\Services\Bot\Scenario\StepRegistry;
use Tests\BotTestCase;
use Tests\Mocks\Bot\FakeMessenger;

class ScenarioRouterTest extends BotTestCase
{
    public function test_router_executes_found_step()
    {
        $step = new class implements StepInterface {

            public function handle(BotContext $context): \App\DTO\Bot\Scenarios\StepResultDTO
            {
                return \App\Services\Bot\Scenario\StepResultFactory::finish();
            }

            public static function stepKey(): string
            {
                return 'testStep';
            }

            public function show(
                BotContext $context,
                ?string $error = null
            ): void {
            }
        };


        app()->instance(
            get_class($step),
            $step
        );


        $registry = new StepRegistry([
            'testStep' => get_class($step),
        ]);


        $executor = $this->mock(
            StepExecutorInterface::class
        );


        $executor
            ->shouldReceive('execute')
            ->once()
            ->with(
                $step,
                \Mockery::type(BotContext::class)
            );


        $router = new ScenarioRouter(
            $registry,
            $executor,
            $this->mock(SysTextInterface::class)
        );


        $context = $this->makeBotContext(
            messenger: new FakeMessenger()
        );


        $context->scenarioDTO = new \App\DTO\Bot\State\StepStateDTO(
            userSocialId: 1,
            scenario: 'test',
            step: 'testStep',
            message: null,
            data: '{}',
            additionalInfo: null,
            commonEntityId: null,
        );


        $router->route($context);
    }
}
