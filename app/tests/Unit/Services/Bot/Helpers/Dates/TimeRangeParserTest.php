<?php

namespace Tests\Unit\Services\Bot\Helpers\Dates;

use App\Services\Bot\Helpers\Dates\TimeRangeParser;
use Tests\TestCase;

class TimeRangeParserTest extends TestCase
{
    public function test_parses_single_time()
    {
        $result = TimeRangeParser::parse(
            '15:30'
        );

        $this->assertEquals(
            [
                'time_start' => '15:30',
                'time_end' => null,
            ],
            $result
        );
    }


    public function test_parses_time_range()
    {
        $result = TimeRangeParser::parse(
            '09:00-18:30'
        );

        $this->assertEquals(
            [
                'time_start' => '09:00',
                'time_end' => '18:30',
            ],
            $result
        );
    }


    public function test_parses_range_with_spaces()
    {
        $result = TimeRangeParser::parse(
            '09:00 - 18:30'
        );

        $this->assertEquals(
            [
                'time_start' => '09:00',
                'time_end' => '18:30',
            ],
            $result
        );
    }


    public function test_parses_range_with_dash_variants()
    {
        foreach ([
                     '09:00–18:30',
                     '09:00—18:30',
                 ] as $input) {

            $result = TimeRangeParser::parse($input);

            $this->assertEquals(
                [
                    'time_start' => '09:00',
                    'time_end' => '18:30',
                ],
                $result
            );
        }
    }


    public function test_normalizes_hours()
    {
        $result = TimeRangeParser::parse(
            '8:05-9:30'
        );

        $this->assertEquals(
            [
                'time_start' => '08:05',
                'time_end' => '09:30',
            ],
            $result
        );
    }


    public function test_returns_false_for_invalid_hour()
    {
        $result = TimeRangeParser::parse(
            '25:00'
        );

        $this->assertFalse($result);
    }


    public function test_returns_false_for_invalid_minutes()
    {
        $result = TimeRangeParser::parse(
            '12:99'
        );

        $this->assertFalse($result);
    }


    public function test_returns_false_for_invalid_format()
    {
        $result = TimeRangeParser::parse(
            'hello'
        );

        $this->assertFalse($result);
    }


    public function test_returns_false_for_partial_range()
    {
        $result = TimeRangeParser::parse(
            '10:00-'
        );

        $this->assertFalse($result);
    }
}
