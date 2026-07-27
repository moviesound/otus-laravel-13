<?php

namespace Tests\Unit\Services\Bot\Helpers\Dates\Planning;

use App\DTO\Bot\Scenarios\Planning\DailyDateDataDTO;
use App\DTO\Bot\Scenarios\Planning\NoRepeatDateDataDTO;
use App\DTO\Bot\Scenarios\Planning\WeeklyDateDataDTO;
use App\DTO\Bot\User\UserDTO;
use App\Enums\Bot\RepeatType;
use App\Services\Bot\Helpers\Dates\Planning\PlanningDateInputFactory;
use Tests\TestCase;

class PlanningDateInputFactoryTest extends TestCase
{
    private PlanningDateInputFactory $factory;


    protected function setUp(): void
    {
        parent::setUp();

        $this->factory = new PlanningDateInputFactory();
    }


    private function makeUser(): UserDTO
    {
        return new UserDTO(
            id: 1,
            name: 'Тест',
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
            eveningTimeWorkdays: '21:00',
            eveningTimeHolidays: '22:00',
            morningDigestStatus: 1,
            eveningDigestStatus: 1,
            digestCurrencies: 0,
            digestWeather: 1,
            userSocials: [],
        );
    }


    public function test_creates_none_repeat_input(): void
    {
        $result = $this->factory->fromUserAndData(
            $this->makeUser(),
            [
                'repeat_type' => 'none',
                'type' => 'task',
                'subType' => 'default',
                'title' => 'Test',
                'deadline_date' => [
                    'year' => 2026,
                    'month' => 7,
                    'day' => 10,
                ],
            ]
        );


        $this->assertEquals(
            'none',
            $result->context->repeatType
        );


        $this->assertInstanceOf(
            NoRepeatDateDataDTO::class,
            $result->dates
        );


        $this->assertEquals(
            2026,
            $result->dates->deadlineDate->year
        );
    }


    public function test_creates_daily_repeat_input(): void
    {
        $result = $this->factory->fromUserAndData(
            $this->makeUser(),
            [
                'repeat_type' => 'daily',

                'repeat_interval' => 3,

                'type' => 'task',
                'subType' => 'default',
                'title' => 'Daily task',

                'deadline_date' => [
                    'month' => 7,
                    'day' => 10,
                ],
            ]
        );


        $this->assertInstanceOf(
            DailyDateDataDTO::class,
            $result->dates
        );


        $this->assertEquals(
            3,
            $result->dates->repeatInterval
        );
    }


    public function test_creates_weekly_rules_from_array(): void
    {
        $result = $this->factory->fromUserAndData(
            $this->makeUser(),
            [
                'repeat_type' => 'weekly',

                'week_days' => [
                    1,
                    5,
                ],

                'weekly_common_time' => [
                    'time_start' => '10:00',
                    'time_end' => '11:00',
                ],

                'type' => 'task',
                'subType' => 'default',
                'title' => 'Weekly',
            ]
        );


        $this->assertInstanceOf(
            WeeklyDateDataDTO::class,
            $result->dates
        );


        $this->assertEquals(
            [1, 5],
            $result->dates->weekDays
        );


        $this->assertEquals(
            '10:00',
            $result->dates->weeklyCommonTime->timeStart
        );
    }


    public function test_parses_date_parts(): void
    {
        $result = $this->factory->fromUserAndData(
            $this->makeUser(),
            [
                'repeat_type' => 'daily',

                'deadline_date' => [
                    'year' => '2026',
                    'month' => '8',
                    'day' => '15',
                    'hour' => '12',
                    'minute' => '30',
                ],

                'type' => 'task',
                'subType' => 'default',
                'title' => 'Date',
            ]
        );


        $date = $result->dates->deadlineDate;


        $this->assertEquals(2026, $date->year);
        $this->assertEquals(8, $date->month);
        $this->assertEquals(15, $date->day);
        $this->assertEquals(12, $date->hour);
        $this->assertEquals(30, $date->minute);
    }


    public function test_uses_default_values_when_missing(): void
    {
        $result = $this->factory->fromUserAndData(
            $this->makeUser(),
            [
                'type' => 'task',
                'subType' => 'default',
                'title' => 'Defaults',
            ]
        );


        $this->assertEquals(
            'none',
            $result->context->repeatType
        );


        $this->assertEquals(
            'Europe/Moscow',
            $result->context->timezone
        );
    }


    public function test_throws_exception_for_unknown_repeat_type(): void
    {
        $this->expectException(\InvalidArgumentException::class);


        $this->factory->fromUserAndData(
            $this->makeUser(),
            [
                'repeat_type' => 'unknown',

                'type' => 'task',
                'subType' => 'default',
                'title' => 'Wrong',
            ]
        );
    }
}
