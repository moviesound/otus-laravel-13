<?php

namespace Tests\Unit\Services\Bot\Helpers\Dates\Planning;

use App\DTO\Bot\User\UserDefaultTimeDTO;
use App\Services\Bot\Helpers\Dates\Planning\TimeResolver;
use Carbon\CarbonImmutable;
use Tests\TestCase;

class TimeResolverTest extends TestCase
{
    private TimeResolver $resolver;


    protected function setUp(): void
    {
        parent::setUp();

        $this->resolver = new TimeResolver();
    }


    private function makeDefaultTime(): UserDefaultTimeDTO
    {
        return new UserDefaultTimeDTO(
            morningWorkdays: '08:30',
            morningHolidays: '10:00',
            eveningWorkdays: '21:00',
            eveningHolidays: '22:00',
        );
    }


    public function test_returns_workday_morning_time(): void
    {
        // Monday
        $date = CarbonImmutable::parse(
            '2026-07-27'
        );


        $result = $this->resolver->defaultHourMinuteForDate(
            $date,
            $this->makeDefaultTime(),
            'morning'
        );


        $this->assertEquals(
            [8, 30],
            $result
        );
    }


    public function test_returns_holiday_morning_time(): void
    {
        // Saturday
        $date = CarbonImmutable::parse(
            '2026-07-25'
        );


        $result = $this->resolver->defaultHourMinuteForDate(
            $date,
            $this->makeDefaultTime(),
            'morning'
        );


        $this->assertEquals(
            [10, 0],
            $result
        );
    }


    public function test_returns_workday_evening_time(): void
    {
        // Monday
        $date = CarbonImmutable::parse(
            '2026-07-27'
        );


        $result = $this->resolver->defaultHourMinuteForDate(
            $date,
            $this->makeDefaultTime(),
            'evening'
        );


        $this->assertEquals(
            [21, 0],
            $result
        );
    }


    public function test_returns_holiday_evening_time(): void
    {
        // Sunday
        $date = CarbonImmutable::parse(
            '2026-07-26'
        );


        $result = $this->resolver->defaultHourMinuteForDate(
            $date,
            $this->makeDefaultTime(),
            'evening'
        );


        $this->assertEquals(
            [22, 0],
            $result
        );
    }


    public function test_uses_fallback_for_unknown_type(): void
    {
        $date = CarbonImmutable::parse(
            '2026-07-27'
        );


        $result = $this->resolver->defaultHourMinuteForDate(
            $date,
            $this->makeDefaultTime(),
            'unknown'
        );


        $this->assertEquals(
            [21, 0],
            $result
        );
    }


    public function test_get_time_by_date_returns_start_for_workday(): void
    {
        $date = CarbonImmutable::parse(
            '2026-07-27'
        );


        $result = $this->resolver->getTimeByDate(
            $date,
            $this->makeDefaultTime(),
            'start'
        );


        $this->assertEquals(
            '08:30',
            $result
        );
    }


    public function test_get_time_by_date_returns_end_for_holiday(): void
    {
        $date = CarbonImmutable::parse(
            '2026-07-25'
        );


        $result = $this->resolver->getTimeByDate(
            $date,
            $this->makeDefaultTime(),
            'end'
        );


        $this->assertEquals(
            '22:00',
            $result
        );
    }


    public function test_parse_time(): void
    {
        $result = $this->resolver->parseTime(
            '18:45'
        );


        $this->assertEquals(
            [18, 45],
            $result
        );
    }


    public function test_parse_time_uses_default_when_null(): void
    {
        $result = $this->resolver->parseTime(
            null,
            '09:30'
        );


        $this->assertEquals(
            [9, 30],
            $result
        );
    }


    public function test_parse_time_handles_empty_string(): void
    {
        $result = $this->resolver->parseTime(
            '',
            '07:15'
        );


        $this->assertEquals(
            [7, 15],
            $result
        );
    }
}
