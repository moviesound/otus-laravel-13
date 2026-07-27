<?php

namespace Tests\Feature\Services\Bot\Scenario;

use App\DTO\Bot\State\StepStateDTO;
use App\Services\Bot\Scenario\Planning\Steps\SelectPlanTypeStep;
use App\Services\Bot\Scenario\ScenarioResolver;
use Tests\TestCase;

class ScenarioResolverTest extends TestCase
{
    public function test_returns_existing_state()
    {
        $resolver = new ScenarioResolver();

        $state = new StepStateDTO(
            userSocialId: 1,
            scenario: 'onPlanning',
            step: 'someStep',
            message: null,
            data: null,
            additionalInfo: null,
            commonEntityId: null,
        );


        $result = $resolver->resolve(
            $state,
            1,
            '/start'
        );


        $this->assertSame(
            $state,
            $result
        );
    }


    public function test_start_command_starts_onboarding()
    {
        $resolver = new ScenarioResolver();


        $result = $resolver->resolve(
            null,
            123,
            '/start'
        );


        $this->assertEquals(
            'onBoarding',
            $result->scenario
        );


        $this->assertEquals(
            'askName',
            $result->step
        );


        $this->assertEquals(
            123,
            $result->userSocialId
        );
    }


    public function test_planning_commands_start_planning_scenario()
    {
        $resolver = new ScenarioResolver();


        foreach ([
                     '/task',
                     '/event',
                     '/plan',
                 ] as $message) {

            $result = $resolver->resolve(
                null,
                1,
                $message
            );


            $this->assertEquals(
                'onPlanning',
                $result->scenario
            );


            $this->assertEquals(
                SelectPlanTypeStep::STEP_KEY,
                $result->step
            );
        }
    }


    public function test_unknown_command_returns_unknown_scenario()
    {
        $resolver = new ScenarioResolver();


        $result = $resolver->resolve(
            null,
            1,
            '/unknown'
        );


        $this->assertEquals(
            'unknown',
            $result->scenario
        );


        $this->assertEquals(
            'unknown',
            $result->step
        );
    }
}
