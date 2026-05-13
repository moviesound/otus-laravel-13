<?php

namespace Tests\Unit\Support;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\TestCase;
use App\Support\CustomHelper;
use Carbon\Carbon;
use DateTimeInterface;
use Faker\Factory as FakerFactory;
use InvalidArgumentException;

class CustomHelperTest extends TestCase
{
    use RefreshDatabase;

    // normalizeDate

    public function test_normalize_date_returns_carbon_from_string()
    {
        $result = CustomHelper::normalizeDate('2026-05-13');

        $this->assertInstanceOf(DateTimeInterface::class, $result);
        $this->assertEquals('2026-05-13', $result->format('Y-m-d'));
    }

    public function test_normalize_date_returns_same_datetime_object()
    {
        $date = Carbon::now();

        $result = CustomHelper::normalizeDate($date);

        $this->assertSame($date, $result);
    }

    public function test_normalize_date_throws_exception_for_integer()
    {
        $this->expectException(InvalidArgumentException::class);

        CustomHelper::normalizeDate(123);
    }

    public function test_normalize_date_throws_exception_for_random_string()
    {
        $faker = FakerFactory::create();

        $randomString = $faker->word();

        $this->expectException(InvalidArgumentException::class);

        CustomHelper::normalizeDate($randomString);
    }

    // makeDifferentTime

    public function test_make_different_time_returns_json_for_valid_days()
    {
        $days = [1, 5];

        $result = CustomHelper::makeDifferentTime($days);

        $this->assertIsString($result);

        $decoded = json_decode($result, true);

        $this->assertIsArray($decoded);

        $this->assertArrayHasKey('1', $decoded);
        $this->assertArrayHasKey('5', $decoded);

        $this->assertArrayHasKey('time_start', $decoded['1']);
        $this->assertArrayHasKey('time_end', $decoded['1']);
    }

    public function test_make_different_time_returns_empty_string_when_null()
    {
        $result = CustomHelper::makeDifferentTime(null);

        $this->assertSame('', $result);
    }

    // makeCommonTime
    public function test_make_common_time_returns_valid_json()
    {
        $result = CustomHelper::makeCommonTime();

        $this->assertIsString($result);

        $decoded = json_decode($result, true);

        $this->assertIsArray($decoded);

        $this->assertArrayHasKey('time_start', $decoded);
        $this->assertArrayHasKey('time_end', $decoded);

        $this->assertMatchesRegularExpression(
            '/^\d{2}:00$/',
            $decoded['time_start']
        );

        $this->assertMatchesRegularExpression(
            '/^\d{2}:00$/',
            $decoded['time_end']
        );
    }

    // randomWeekDays
    public function test_random_week_days_returns_valid_days()
    {
        $result = CustomHelper::randomWeekDays();

        $this->assertIsArray($result);

        $this->assertGreaterThanOrEqual(1, count($result));
        $this->assertLessThanOrEqual(3, count($result));

        foreach ($result as $day) {
            $this->assertIsInt($day);

            $this->assertGreaterThanOrEqual(1, $day);
            $this->assertLessThanOrEqual(7, $day);
        }
    }

    public function test_random_month_days_returns_valid_days()
    {
        $result = CustomHelper::randomMonthDays();

        $this->assertIsArray($result);

        $this->assertGreaterThanOrEqual(1, count($result));
        $this->assertLessThanOrEqual(3, count($result));

        foreach ($result as $day) {
            $this->assertIsInt($day);

            $this->assertGreaterThanOrEqual(1, $day);
            $this->assertLessThanOrEqual(28, $day);
        }
    }
}
