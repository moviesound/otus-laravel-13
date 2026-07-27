<?php

namespace Tests\Unit\Services\Bot\Scenario;

use App\Enums\Bot\StepResultType;
use App\Services\Bot\Scenario\StepResultFactory;
use Tests\TestCase;

class StepResultFactoryTest extends TestCase
{
    public function test_switch_creates_switch_result()
    {
        $result = StepResultFactory::switch(
            'nextStep',
            [
                'key' => 'value',
            ],
            'additional'
        );


        $this->assertEquals(
            StepResultType::SWITCH,
            $result->type
        );


        $this->assertEquals(
            'nextStep',
            $result->switchStep
        );


        $this->assertEquals(
            [
                'key' => 'value',
            ],
            $result->data
        );


        $this->assertEquals(
            'additional',
            $result->additionalInfo
        );
    }


    public function test_switch_uses_empty_data_by_default()
    {
        $result = StepResultFactory::switch(
            'nextStep'
        );


        $this->assertEquals(
            StepResultType::SWITCH,
            $result->type
        );


        $this->assertEquals(
            'nextStep',
            $result->switchStep
        );


        $this->assertEquals(
            [],
            $result->data
        );


        $this->assertNull(
            $result->additionalInfo
        );
    }


    public function test_repeat_creates_repeat_result()
    {
        $result = StepResultFactory::repeat(
            'wrong value'
        );


        $this->assertEquals(
            StepResultType::REPEAT,
            $result->type
        );


        $this->assertEquals(
            'wrong value',
            $result->error
        );
    }


    public function test_repeat_can_be_without_error()
    {
        $result = StepResultFactory::repeat();


        $this->assertEquals(
            StepResultType::REPEAT,
            $result->type
        );


        $this->assertNull(
            $result->error
        );
    }


    public function test_finish_creates_finish_result()
    {
        $result = StepResultFactory::finish();


        $this->assertEquals(
            StepResultType::FINISH,
            $result->type
        );


        $this->assertNull(
            $result->switchStep
        );


        $this->assertNull(
            $result->error
        );
    }
}
