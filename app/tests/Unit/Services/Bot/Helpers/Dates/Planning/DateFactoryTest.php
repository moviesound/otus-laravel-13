<?php

namespace Tests\Unit\Services\Bot\Helpers\Dates\Planning;

use App\Services\Bot\Helpers\Dates\Planning\DateFactory;
use Carbon\CarbonImmutable;
use DateTimeZone;
use Tests\TestCase;

class DateFactoryTest extends TestCase
{
    private DateFactory $factory;


    protected function setUp(): void
    {
        parent::setUp();

        $this->factory = new DateFactory();
    }


    public function test_creates_safe_date(): void
    {
        $date = $this->factory->safeDate(
            year: 2026,
            month: 7,
            day: 15,
            hour: 12,
            minute: 30,
            second: 0,
            timezone: 'UTC',
        );


        $this->assertInstanceOf(
            CarbonImmutable::class,
            $date
        );


        $this->assertEquals(
            '2026-07-15 12:30:00',
            $date->format('Y-m-d H:i:s')
        );


        $this->assertEquals(
            'UTC',
            $date->getTimezone()->getName()
        );
    }


    public function test_clamps_invalid_day_to_last_day_of_month(): void
    {
        $date = $this->factory->safeDate(
            year: 2026,
            month: 2,
            day: 31,
            hour: 10,
            minute: 0,
            second: 0,
            timezone: 'UTC',
        );


        $this->assertEquals(
            '2026-02-28 10:00:00',
            $date->format('Y-m-d H:i:s')
        );
    }


    public function test_clamps_invalid_month(): void
    {
        $date = $this->factory->safeDate(
            year: 2026,
            month: 15,
            day: 10,
            hour: 10,
            minute: 0,
            second: 0,
            timezone: 'UTC',
        );


        $this->assertEquals(
            '2026-12-10 10:00:00',
            $date->format('Y-m-d H:i:s')
        );
    }


    public function test_clamps_month_below_one(): void
    {
        $date = $this->factory->safeDate(
            year: 2026,
            month: 0,
            day: 10,
            hour: 10,
            minute: 0,
            second: 0,
            timezone: 'UTC',
        );


        $this->assertEquals(
            '2026-01-10 10:00:00',
            $date->format('Y-m-d H:i:s')
        );
    }


    public function test_returns_days_in_month(): void
    {
        $days = $this->factory->daysInMonth(
            year: 2026,
            month: 2,
            timezone: 'UTC',
        );


        $this->assertEquals(
            28,
            $days
        );
    }


    public function test_returns_leap_year_days_in_month(): void
    {
        $days = $this->factory->daysInMonth(
            year: 2024,
            month: 2,
            timezone: new DateTimeZone('UTC'),
        );


        $this->assertEquals(
            29,
            $days
        );
    }


    public function test_accepts_timezone_object(): void
    {
        $timezone = new DateTimeZone(
            'Europe/Amsterdam'
        );


        $date = $this->factory->safeDate(
            year: 2026,
            month: 7,
            day: 1,
            hour: 12,
            minute: 0,
            second: 0,
            timezone: $timezone,
        );


        $this->assertEquals(
            'Europe/Amsterdam',
            $date->getTimezone()->getName()
        );
    }


    public function test_returns_current_time_for_timezone(): void
    {
        CarbonImmutable::setTestNow(
            CarbonImmutable::parse(
                '2026-07-27 12:00:00',
                'UTC'
            )
        );


        $now = $this->factory->now('UTC');


        $this->assertEquals(
            '2026-07-27 12:00:00',
            $now->format('Y-m-d H:i:s')
        );


        CarbonImmutable::setTestNow();
    }
}
