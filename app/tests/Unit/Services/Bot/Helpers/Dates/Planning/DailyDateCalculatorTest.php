<?php

namespace Tests\Unit\Services\Bot\Helpers\Dates\Planning;

use App\DTO\Bot\Scenarios\Planning\CommonDateContextDTO;
use App\DTO\Bot\Scenarios\Planning\DailyDateDataDTO;
use App\DTO\Bot\Scenarios\Planning\DatePartsDTO;
use App\DTO\Bot\User\UserDefaultTimeDTO;
use App\Services\Bot\Helpers\Dates\Planning\DailyDateCalculator;
use App\Services\Bot\Helpers\Dates\Planning\DateFactory;
use App\Services\Bot\Helpers\Dates\Planning\TimeResolver;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Tests\TestCase;

class DailyDateCalculatorTest extends TestCase
{
    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }


    private function makeCalculator(
        string $now,
    ): DailyDateCalculator {

        Carbon::setTestNow(
            CarbonImmutable::parse($now, 'UTC')
        );

        return new DailyDateCalculator(
            new DateFactory(),
            new TimeResolver(),
        );
    }


    private function makeContext(): CommonDateContextDTO
    {
        return new CommonDateContextDTO(
            timezone: 'UTC',

            defaultTime: new UserDefaultTimeDTO(
                morningWorkdays: '08:00',
                morningHolidays: '10:00',
                eveningWorkdays: '18:00',
                eveningHolidays: '18:00',
            ),

            repeatType: 'none'
        );
    }


    public function test_returns_deadline_for_future_date(): void
    {
        $calculator = $this->makeCalculator(
            '2026-07-01 10:00'
        );


        $data = new DailyDateDataDTO(
            dateMode: 'deadline',
            repeatInterval: 1,

            deadlineDate: new DatePartsDTO(
                year: 2026,
                month: 7,
                day: 10,
                hour: 12,
                minute: 0,
            ),
        );


        $result = $calculator->calculate(
            $this->makeContext(),
            $data
        );


        $this->assertEquals(
            '2026-07-10 12:00',
            $result->deadline->format('Y-m-d H:i')
        );
    }


    public function test_moves_daily_deadline_to_next_interval(): void
    {
        $calculator = $this->makeCalculator(
            '2026-07-10 15:00'
        );


        $result = $calculator->getNextTaskDate(
            context: $this->makeContext(),

            dateParts: new DatePartsDTO(
                year: 2026,
                month: 7,
                day: 10,
                hour: 10,
                minute: 0,
            ),

            interval: 2
        );


        $this->assertEquals(
            '2026-07-12 10:00',
            $result->format('Y-m-d H:i')
        );
    }


    public function test_uses_default_time_when_user_time_missing(): void
    {
        $calculator = $this->makeCalculator(
            '2026-07-01 10:00'
        );


        $result = $calculator->getNextTaskDate(
            context: $this->makeContext(),

            dateParts: new DatePartsDTO(
                year: 2026,
                month: 7,
                day: 10,
            ),

            interval: 1,
        );


        $this->assertEquals(
            '2026-07-10 08:00',
            $result->format('Y-m-d H:i')
        );
    }


    public function test_returns_empty_period_when_interval_invalid(): void
    {
        $calculator = $this->makeCalculator(
            '2026-07-01 10:00'
        );


        $data = new DailyDateDataDTO(
            dateMode: 'period',
            repeatInterval: 0,

            periodStart: new DatePartsDTO(
                year: 2026,
                month: 7,
                day: 10,
            ),
        );


        $result = $calculator->calculate(
            $this->makeContext(),
            $data
        );


        $this->assertNull(
            $result->periodStart
        );

        $this->assertNull(
            $result->periodEnd
        );
    }


    public function test_calculates_period_duration(): void
    {
        $calculator = $this->makeCalculator(
            '2026-07-01 10:00'
        );


        $data = new DailyDateDataDTO(
            dateMode: 'period',
            repeatInterval: 1,

            periodStart: new DatePartsDTO(
                year: 2026,
                month: 7,
                day: 10,
                hour: 10,
                minute: 0,
            ),

            periodEnd: new DatePartsDTO(
                year: 2026,
                month: 7,
                day: 10,
                hour: 12,
                minute: 0,
            ),
        );


        $result = $calculator->calculate(
            $this->makeContext(),
            $data
        );


        $this->assertEquals(
            '2026-07-10 10:00',
            $result->periodStart->format('Y-m-d H:i')
        );


        $this->assertEquals(
            '2026-07-10 18:00',
            $result->periodEnd->format('Y-m-d H:i')
        );
    }
}
