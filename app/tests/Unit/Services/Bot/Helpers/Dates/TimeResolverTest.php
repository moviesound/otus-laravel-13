<?php

namespace Tests\Unit\Services\Bot\Helpers\Dates;

use App\DTO\Bot\User\UserDTO;
use App\Services\Bot\Helpers\Dates\TimeResolver;
use Tests\TestCase;

class TimeResolverTest extends TestCase
{
    public function test_resolves_workday_time()
    {
        $user = $this->makeUser(
            eveningTimeWorkdays: '21:30',
            eveningTimeHolidays: '23:00',
        );

        // 2026-07-27 Monday
        $result = app(TimeResolver::class)->resolve(
            $user,
            [
                'year' => 2026,
                'month' => 7,
                'day' => 27,
            ]
        );

        $this->assertEquals(
            [
                'hour' => 21,
                'minute' => 30,
            ],
            $result
        );
    }


    public function test_resolves_holiday_time()
    {
        $user = $this->makeUser(
            eveningTimeWorkdays: '21:30',
            eveningTimeHolidays: '23:00',
        );

        // 2026-07-25 Saturday
        $result = app(TimeResolver::class)->resolve(
            $user,
            [
                'year' => 2026,
                'month' => 7,
                'day' => 25,
            ]
        );

        $this->assertEquals(
            [
                'hour' => 23,
                'minute' => 0,
            ],
            $result
        );
    }


    public function test_parses_single_digit_time_parts()
    {
        $user = $this->makeUser(
            eveningTimeWorkdays: '8:05',
            eveningTimeHolidays: '10:00',
        );

        $result = app(TimeResolver::class)->resolve(
            $user,
            [
                'year' => 2026,
                'month' => 7,
                'day' => 27,
            ]
        );

        $this->assertEquals(
            [
                'hour' => 8,
                'minute' => 5,
            ],
            $result
        );
    }


    private function makeUser(
        string $eveningTimeWorkdays,
        string $eveningTimeHolidays,
    ): UserDTO {
        return new UserDTO(
            id: 1,
            name: 'Test',
            sex: null,
            email: null,
            phone: null,
            phoneProved: 0,
            speaker: 'marina',
            tariffId: 1,
            language: 'ru',
            timezone: 'Europe/Moscow',
            locationId: null,
            birthDay: null,
            birthMonth: null,
            birthYear: null,
            politicsAgreed: 1,
            morningTimeWorkdays: '08:00',
            morningTimeHolidays: '10:00',
            eveningTimeWorkdays: $eveningTimeWorkdays,
            eveningTimeHolidays: $eveningTimeHolidays,
            morningDigestStatus: 1,
            eveningDigestStatus: 1,
            digestCurrencies: 0,
            digestWeather: 1,
            userSocials: [],
        );
    }
}
