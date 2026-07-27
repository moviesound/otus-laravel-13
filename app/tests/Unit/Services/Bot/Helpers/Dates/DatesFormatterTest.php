<?php

namespace Tests\Unit\Services\Bot\Helpers\Dates;

use App\Services\Bot\Helpers\Dates\DatesFormatter;
use App\Services\Bot\Messengers\MessengerTextResolver;
use Tests\TestCase;

class DatesFormatterTest extends TestCase
{
    private function formatter(): DatesFormatter
    {
        return new DatesFormatter(
            app(MessengerTextResolver::class)
        );
    }


    public function test_formats_full_date()
    {
        $formatter = $this->formatter();

        $result = $formatter->human(
            [
                'day' => 18,
                'month' => 7,
                'year' => 2026,
                'hour' => 15,
                'minute' => 30,
            ],
            'telegram',
            'ru'
        );

        $this->assertEquals(
            '18 Июля 2026 15:30',
            $result
        );
    }


    public function test_formats_date_without_time()
    {
        $formatter = $this->formatter();

        $result = $formatter->human(
            [
                'day' => 5,
                'month' => 3,
                'year' => 2026,
            ],
            'telegram',
            'ru'
        );

        $this->assertEquals(
            '5 Марта 2026',
            $result
        );
    }


    public function test_formats_only_time()
    {
        $formatter = $this->formatter();

        $result = $formatter->human(
            [
                'hour' => 8,
                'minute' => 5,
            ],
            'telegram',
            'ru'
        );

        $this->assertEquals(
            '08:05',
            $result
        );
    }


    public function test_returns_empty_string_for_empty_date()
    {
        $formatter = $this->formatter();

        $this->assertEquals(
            '',
            $formatter->human(
                [],
                'telegram',
                'ru'
            )
        );
    }


    public function test_returns_empty_for_invalid_month()
    {
        $formatter = $this->formatter();

        $this->assertEquals(
            '',
            $formatter->monthName(
                13,
                'telegram',
                'ru'
            )
        );
    }


    public function test_returns_weekday_name()
    {
        $formatter = $this->formatter();

        $this->assertNotEmpty(
            $formatter->weekdayName(
                1,
                'telegram',
                'ru'
            )
        );
    }
}
