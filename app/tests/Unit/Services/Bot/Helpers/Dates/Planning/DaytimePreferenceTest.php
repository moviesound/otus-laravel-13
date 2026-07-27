<?php

namespace Tests\Unit\Services\Bot\Helpers\Dates\Planning;

use App\Services\Bot\Helpers\Dates\Planning\DaytimePreference;
use Carbon\CarbonImmutable;
use Tests\TestCase;

class DaytimePreferenceTest extends TestCase
{
    private DaytimePreference $preference;


    protected function setUp(): void
    {
        parent::setUp();

        $this->preference = new DaytimePreference();
    }


    public function test_moves_morning_candidate_to_noon_inside_window(): void
    {
        $result = $this->preference->apply(
            candidate: CarbonImmutable::parse(
                '2026-07-27 08:00'
            ),
            windowStart: CarbonImmutable::parse(
                '2026-07-27 08:00'
            ),
            windowEnd: CarbonImmutable::parse(
                '2026-07-27 18:00'
            ),
        );


        $this->assertEquals(
            '2026-07-27 12:00',
            $result->format('Y-m-d H:i')
        );
    }


    public function test_keeps_candidate_when_already_daytime(): void
    {
        $result = $this->preference->apply(
            candidate: CarbonImmutable::parse(
                '2026-07-27 15:00'
            ),
            windowStart: CarbonImmutable::parse(
                '2026-07-27 08:00'
            ),
            windowEnd: CarbonImmutable::parse(
                '2026-07-27 18:00'
            ),
        );


        $this->assertEquals(
            '2026-07-27 15:00',
            $result->format('Y-m-d H:i')
        );
    }


    public function test_keeps_candidate_for_night_window(): void
    {
        $result = $this->preference->apply(
            candidate: CarbonImmutable::parse(
                '2026-07-27 22:00'
            ),
            windowStart: CarbonImmutable::parse(
                '2026-07-27 18:00'
            ),
            windowEnd: CarbonImmutable::parse(
                '2026-07-28 06:00'
            ),
        );


        $this->assertEquals(
            '2026-07-27 22:00',
            $result->format('Y-m-d H:i')
        );
    }


    public function test_keeps_candidate_when_noon_outside_window(): void
    {
        $result = $this->preference->apply(
            candidate: CarbonImmutable::parse(
                '2026-07-27 07:00'
            ),
            windowStart: CarbonImmutable::parse(
                '2026-07-27 06:00'
            ),
            windowEnd: CarbonImmutable::parse(
                '2026-07-27 10:00'
            ),
        );


        $this->assertEquals(
            '2026-07-27 07:00',
            $result->format('Y-m-d H:i')
        );
    }


    public function test_moves_candidate_to_noon_when_window_contains_noon(): void
    {
        $result = $this->preference->apply(
            candidate: CarbonImmutable::parse(
                '2026-07-27 10:00'
            ),
            windowStart: CarbonImmutable::parse(
                '2026-07-27 09:00'
            ),
            windowEnd: CarbonImmutable::parse(
                '2026-07-27 17:00'
            ),
        );


        $this->assertEquals(
            '2026-07-27 12:00',
            $result->format('Y-m-d H:i')
        );
    }
}
