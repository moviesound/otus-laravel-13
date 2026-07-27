<?php

namespace Tests\Unit\Services\Bot\Helpers\Dates;

use App\Services\Bot\Helpers\Dates\DatesParser;
use Tests\TestCase;

class DatesParserTest extends TestCase
{
    public function test_parses_full_date_with_time()
    {
        $result = DatesParser::parseDateTimeString(
            '18 июля 2026 15:30'
        );

        $this->assertEquals(
            [
                'year' => 2026,
                'month' => 7,
                'day' => 18,
                'hour' => 15,
                'minute' => 30,
            ],
            $result
        );
    }


    public function test_parses_numeric_date_with_default_year()
    {
        $result = DatesParser::parseDateTimeString(
            '05.03',
            2026
        );

        $this->assertEquals(
            [
                'year' => 2026,
                'month' => 3,
                'day' => 5,
                'hour' => null,
                'minute' => null,
            ],
            $result
        );
    }


    public function test_parses_slash_date()
    {
        $result = DatesParser::parseDateTimeString(
            '05/03/2026'
        );

        $this->assertEquals(
            [
                'year' => 2026,
                'month' => 3,
                'day' => 5,
                'hour' => null,
                'minute' => null,
            ],
            $result
        );
    }


    public function test_parses_month_day_format()
    {
        $result = DatesParser::parseDateTimeString(
            '3-5',
            2026
        );

        $this->assertEquals(
            [
                'year' => 2026,
                'month' => 3,
                'day' => 5,
                'hour' => null,
                'minute' => null,
            ],
            $result
        );
    }


    public function test_parses_month_name_before_day()
    {
        $result = DatesParser::parseDateTimeString(
            'июля 18 2026'
        );

        $this->assertEquals(
            [
                'year' => 2026,
                'month' => 7,
                'day' => 18,
                'hour' => null,
                'minute' => null,
            ],
            $result
        );
    }


    public function test_parses_english_month()
    {
        $result = DatesParser::parseDateTimeString(
            '18 july 2026'
        );

        $this->assertEquals(
            [
                'year' => 2026,
                'month' => 7,
                'day' => 18,
                'hour' => null,
                'minute' => null,
            ],
            $result
        );
    }


    public function test_returns_null_for_invalid_date()
    {
        $result = DatesParser::parseDateTimeString(
            'hello world'
        );

        $this->assertNull($result);
    }


    public function test_parses_only_time_with_date()
    {
        $result = DatesParser::parseDateTimeString(
            '5 марта 09:05',
            2026
        );

        $this->assertEquals(
            [
                'year' => 2026,
                'month' => 3,
                'day' => 5,
                'hour' => 9,
                'minute' => 5,
            ],
            $result
        );
    }
}
