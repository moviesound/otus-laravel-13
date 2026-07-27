<?php

namespace Tests\Unit\Services\Bot\Scenario;

use App\Contracts\Bot\Scenario\Steps\StepInterface;
use App\Services\Bot\Scenario\StepRegistry;
use Tests\TestCase;

class StepRegistryTest extends TestCase
{
    public function test_returns_registered_step()
    {
        $step = new class implements StepInterface {

            public function handle(
                \App\Services\Bot\BotContext $context
            ): \App\DTO\Bot\Scenarios\StepResultDTO {
                return \App\Services\Bot\Scenario\StepResultFactory::finish();
            }

            public static function stepKey(): string
            {
                return 'testStep';
            }

            public function show(
                \App\Services\Bot\BotContext $context,
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


        $result = $registry->get(
            'testStep'
        );


        $this->assertSame(
            $step,
            $result
        );
    }


    public function test_unknown_step_throws_exception()
    {
        $registry = new StepRegistry([]);


        $this->expectException(
            \RuntimeException::class
        );


        $this->expectExceptionMessage(
            'Unknown step [unknown]'
        );


        $registry->get(
            'unknown'
        );
    }
}
