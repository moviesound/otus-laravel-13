<?php

namespace Tests\Unit\Services\Bot\Helpers\Dates;

use App\Services\Bot\Helpers\Dates\QuarterPeriodValidator;
use Tests\TestCase;

class QuarterPeriodValidatorTest extends TestCase
{
    public function test_returns_true_when_start_month_before_end_month()
    {
        $result = QuarterPeriodValidator::isValid(
            startMonth: 3,
            startDay: 20,
            endMonth: 5,
            endDay: 10,
        );

        $this->assertTrue($result);
    }


    public function test_returns_false_when_start_month_after_end_month()
    {
        $result = QuarterPeriodValidator::isValid(
            startMonth: 8,
            startDay: 1,
            endMonth: 4,
            endDay: 10,
        );

        $this->assertFalse($result);
    }


    public function test_returns_true_when_same_month_and_start_day_before_end_day()
    {
        $result = QuarterPeriodValidator::isValid(
            startMonth: 5,
            startDay: 10,
            endMonth: 5,
            endDay: 20,
        );

        $this->assertTrue($result);
    }


    public function test_returns_true_when_same_month_and_same_day()
    {
        $result = QuarterPeriodValidator::isValid(
            startMonth: 5,
            startDay: 20,
            endMonth: 5,
            endDay: 20,
        );

        $this->assertTrue($result);
    }


    public function test_returns_false_when_same_month_and_start_day_after_end_day()
    {
        $result = QuarterPeriodValidator::isValid(
            startMonth: 5,
            startDay: 25,
            endMonth: 5,
            endDay: 20,
        );

        $this->assertFalse($result);
    }
}
