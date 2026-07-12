<?php

namespace App\Services\Bot\Helpers\Dates\Planning;

use App\DTO\Bot\Scenarios\Planning\CommonDateContextDTO;
use App\DTO\Bot\Scenarios\Planning\DailyDateDataDTO;
use App\DTO\Bot\Scenarios\Planning\DatePartsDTO;
use App\DTO\Bot\Scenarios\Planning\MonthlyDateDataDTO;
use App\DTO\Bot\Scenarios\Planning\MonthlyTimeRuleDTO;
use App\DTO\Bot\Scenarios\Planning\NoRepeatDateDataDTO;
use App\DTO\Bot\Scenarios\Planning\PlanningCreateInputDTO;
use App\DTO\Bot\Scenarios\Planning\QuarterlyDateDataDTO;
use App\DTO\Bot\Scenarios\Planning\TimePeriodDTO;
use App\DTO\Bot\Scenarios\Planning\WeeklyDateDataDTO;
use App\DTO\Bot\Scenarios\Planning\WeeklyTimeRuleDTO;
use App\DTO\Bot\Scenarios\Planning\YearlyDateDataDTO;
use App\DTO\Bot\User\UserDefaultTimeDTO;
use App\DTO\Bot\User\UserDTO;
use App\Enums\Bot\RepeatType;
use InvalidArgumentException;

final class PlanningDateInputFactory
{
    public function fromUserAndData(UserDTO $user, array $data): PlanningCreateInputDTO
    {
        $repeatType = $data['repeat_type'] ?? RepeatType::None->value;

        $dates = new UserDefaultTimeDTO(
            morningWorkdays: $this->userValue($user, 'morning_time_workdays', '08:00'),
            morningHolidays: $this->userValue($user, 'morning_time_holidays', '09:00'),
            eveningWorkdays: $this->userValue($user, 'evening_time_workdays', '21:00'),
            eveningHolidays: $this->userValue($user, 'evening_time_holidays', '22:00'),
        );

        $context = new CommonDateContextDTO(
            timezone: $this->userValue($user, 'timezone', 'UTC'),
            defaultTime: $dates,
            repeatType: $repeatType,
        );

        $dates = match ($repeatType) {
            'none' => new NoRepeatDateDataDTO(
                dateMode: $data['date_mode'] ?? 'deadline',
                deadlineDate: $this->dateParts($data['deadline_date'] ?? null),
                periodStart: $this->dateParts($data['period_start'] ?? null),
                periodEnd: $this->dateParts($data['period_end'] ?? null),
            ),

            'daily' => new DailyDateDataDTO(
                repeatInterval: max(1, (int)($data['repeat_interval'] ?? 1)),
                dateMode: $data['date_mode'] ?? 'deadline',
                deadlineDate: $this->dateParts($data['deadline_date'] ?? null),
                periodStart: $this->dateParts($data['period_start'] ?? null),
                periodEnd: $this->dateParts($data['period_end'] ?? null),
            ),

            'weekly' => new WeeklyDateDataDTO(
                weekDays: $this->intList($data['week_days'] ?? []),
                weeklyCommonTime: $this->timePeriod($data['weekly_common_time'] ?? null),
                weeklyDifferentTime: $this->weeklyRules($data['weekly_different_time'] ?? null),
            ),

            'monthly' => new MonthlyDateDataDTO(
                monthDays: $this->commaValue($data['month_days'] ?? ''),
                monthlyCommonTime: $this->timePeriod($data['monthly_common_time'] ?? null),
                monthlyDifferentTime: $this->monthlyRules($data['monthly_different_time'] ?? null),
            ),

            'quarterly' => new QuarterlyDateDataDTO(
                quarterType: $data['quarter_type'] ?? 'deadline',
                monthInQuarter: $this->nullableInt($data['month_in_quarter'] ?? null),
                dayInQuarter: $this->nullableInt($data['day_in_quarter'] ?? null),
                startMonthInQuarter: $this->nullableInt($data['start_month_in_quarter'] ?? null),
                startDayInQuarter: $this->nullableInt($data['start_day_in_quarter'] ?? null),
                endMonthInQuarter: $this->nullableInt($data['end_month_in_quarter'] ?? null),
                endDayInQuarter: $this->nullableInt($data['end_day_in_quarter'] ?? null),
            ),

            'yearly' => new YearlyDateDataDTO(
                yearType: $data['year_type'] ?? 'deadline',
                monthInYear: $this->nullableInt($data['month_in_year'] ?? null),
                dayInYear: $this->nullableInt($data['day_in_year'] ?? null),
                startMonthInYear: $this->nullableInt($data['start_month_in_year'] ?? null),
                startDayInYear: $this->nullableInt($data['start_day_in_year'] ?? null),
                endMonthInYear: $this->nullableInt($data['end_month_in_year'] ?? null),
                endDayInYear: $this->nullableInt($data['end_day_in_year'] ?? null),
            ),

            default => throw new InvalidArgumentException("Unknown repeat type: {$repeatType}"),
        };

        return new PlanningCreateInputDTO(
            context: $context,
            dates: $dates,
            reminders: $data['reminders'] ?? [],
            tags: $data['tags'] ?? null,
            type: $data['type'],
            subType: $data['subType'],
            title: $data['title'],
            description: $data['description'] ?? null,
            action: $data['action'] ?? 'add',
        );
    }

    private function dateParts(null|array $value): ?DatePartsDTO
    {
        if (empty($value)) {
            return null;
        }

        return new DatePartsDTO(
            year: $this->nullableInt($value['year'] ?? null),
            month: $this->nullableInt($value['month'] ?? null),
            day: $this->nullableInt($value['day'] ?? null),
            hour: $this->nullableInt($value['hour'] ?? null),
            minute: $this->nullableInt($value['minute'] ?? null),
        );
    }

    private function timePeriod(mixed $value): ?TimePeriodDTO
    {
        $value = $this->normalizeArray($value);

        if ($value === []) {
            return null;
        }

        return new TimePeriodDTO(
            timeStart: $value['time_start'] ?? null,
            timeEnd: $value['time_end'] ?? null,
        );
    }

    /**
     *  формат weekly_different_time обычно такой:
     *
     * [
     *   1 => ['time_start' => '10:00', 'time_end' => '11:00'],
     *   5 => ['time_start' => '14:00']
     * ]
     *
     * @return WeeklyTimeRuleDTO[]|null
     */
    private function weeklyRules(mixed $value): ?array
    {
        $value = $this->normalizeArray($value);

        if ($value === []) {
            return null;
        }

        $rules = [];

        foreach ($value as $weekDay => $time) {
            $time = $this->normalizeArray($time);

            if ($time === []) {
                continue;
            }

            $rules[] = new WeeklyTimeRuleDTO(
                weekDay: (int)$weekDay,
                time: new TimePeriodDTO(
                    timeStart: $time['time_start'] ?? null,
                    timeEnd: $time['time_end'] ?? null,
                ),
            );
        }

        return $rules ?: null;
    }

    /**
     * Поддерживаем 2 формата:
     *
     * [
     *   ['days' => '5', 'time' => ['time_start' => '10:00']]
     * ]
     *
     * [
     *   5 => ['time_start' => '10:00'],
     *   '10-12' => ['time_start' => '11:00', 'time_end' => '15:00']
     * ]
     *
     * @return MonthlyTimeRuleDTO[]|null
     */
    private function monthlyRules(mixed $value): ?array
    {
        $value = $this->normalizeArray($value);

        if ($value === []) {
            return null;
        }

        $rules = [];

        foreach ($value as $key => $row) {
            $row = $this->normalizeArray($row);

            if ($row === []) {
                continue;
            }

            if (isset($row['days'], $row['time'])) {
                $time = $this->normalizeArray($row['time']);

                $rules[] = new MonthlyTimeRuleDTO(
                    days: (string)$row['days'],
                    time: new TimePeriodDTO(
                        timeStart: $time['time_start'] ?? null,
                        timeEnd: $time['time_end'] ?? null,
                    ),
                );

                continue;
            }

            $rules[] = new MonthlyTimeRuleDTO(
                days: (string)$key,
                time: new TimePeriodDTO(
                    timeStart: $row['time_start'] ?? null,
                    timeEnd: $row['time_end'] ?? null,
                ),
            );
        }

        return $rules ?: null;
    }

    private function intList(mixed $value): array
    {
        if (is_string($value)) {
            $value = explode(',', $value);
        }

        if (!is_array($value)) {
            return [];
        }

        return collect($value)
            ->map(fn($item) => (int)$item)
            ->filter(fn(int $item) => $item > 0)
            ->values()
            ->all();
    }

    private function commaValue(mixed $value): string
    {
        if (is_array($value)) {
            return implode(',', $value);
        }

        return (string)$value;
    }

    private function normalizeArray(mixed $value): array
    {
        if (is_array($value)) {
            return $value;
        }

        if (is_string($value) && $value !== '') {
            $decoded = json_decode($value, true);

            return is_array($decoded) ? $decoded : [];
        }

        return [];
    }

    private function nullableInt(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (int)$value;
    }

    private function userValue(UserDTO $user, string $key, mixed $default = null): mixed
    {
        return $user->{$key}
            ?? $default;
    }
}
