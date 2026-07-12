<?php

namespace App\Services\Bot\Helpers\Dates\Planning;

use App\DTO\Bot\User\UserDefaultTimeDTO;
use Carbon\CarbonImmutable;
use DateTimeInterface;
use DateTimeZone;

final readonly class SystemTaskReminderCalculator
{
    public function __construct(
        private AllowedReminderWindowNormalizer $windowNormalizer,
    ) {
    }

    /**
     * $deadlineLocal — дедлайн в локальном timezone пользователя.
     * $baseLocal:
     *   - null => отталкиваемся от now пользователя;
     *   - period_start => если задача с периодом;
     *   - другая локальная дата => если надо.
     *
     * Возвращает UTC дату для хранения в БД.
     */
    public function calculate(
        DateTimeInterface|string|null $deadlineLocal,
        DateTimeInterface|string|null $baseLocal,
        string $timezone,
        UserDefaultTimeDTO $allowedTime,
    ): ?CarbonImmutable {
        if ($this->isEmptyDate($deadlineLocal)) {
            return null;
        }

        $tzUser = new DateTimeZone($timezone);

        $deadline = $this->toLocalCarbon(
            value: $deadlineLocal,
            timezone: $tzUser,
        );

        if ($deadline === null) {
            return null;
        }

        $nowLocal = CarbonImmutable::now($tzUser);

        $base = $this->isEmptyDate($baseLocal)
            ? $nowLocal
            : $this->toLocalCarbon($baseLocal, $tzUser);

        if ($base === null) {
            $base = $nowLocal;
        }

        $daysToDeadline = (int) $base
            ->diff($deadline)
            ->format('%r%a');

        if ($daysToDeadline < 0) {
            return null;
        }

        $intervalDays = $this->intervalDays($daysToDeadline);

        $candidateLocal = $base->addDays($intervalDays);

        $candidateLocal = $this->windowNormalizer->normalize(
            candidateLocal: $candidateLocal,
            allowedTime: $allowedTime,
        );

        /**
         * Не позже дедлайна.
         */
        if ($candidateLocal > $deadline) {
            return null;
        }

        return $candidateLocal->utc();
    }

    public function calculateFromUtcStrings(
        string $deadlineUtcString,
        string $lastRemindUtcString,
        string $timezone,
        UserDefaultTimeDTO $allowedTime,
    ): ?CarbonImmutable {
        if ($deadlineUtcString === '0000-00-00 00:00:00') {
            return null;
        }

        $tzUser = new DateTimeZone($timezone);

        $deadlineLocal = CarbonImmutable::parse(
            $deadlineUtcString,
            'UTC',
        )->setTimezone($tzUser);

        $nowLocal = CarbonImmutable::now($tzUser);

        if ($lastRemindUtcString !== '0000-00-00 00:00:00') {
            $baseLocal = CarbonImmutable::parse(
                $lastRemindUtcString,
                'UTC',
            )->setTimezone($tzUser);
        } else {
            $baseLocal = $nowLocal;
        }

        $daysToDeadline = (int) $baseLocal
            ->diff($deadlineLocal)
            ->format('%r%a');

        if ($daysToDeadline < 0) {
            return null;
        }

        $intervalDays = $this->intervalDays($daysToDeadline);

        $candidateLocal = $baseLocal->addDays($intervalDays);

        $candidateLocal = $this->windowNormalizer->normalize(
            candidateLocal: $candidateLocal,
            allowedTime: $allowedTime,
        );

        if ($candidateLocal > $deadlineLocal) {
            return null;
        }

        return $candidateLocal->utc();
    }

    /**
     * Через сколько дней новое напоминание?
     * Зависит от количества дней до дедлайна
     */
    private function intervalDays(int $daysToDeadline): int
    {
        if ($daysToDeadline > 30) {
            return 12;
        }

        if ($daysToDeadline > 7) {
            return 7;
        }

        if ($daysToDeadline > 3) {
            return 3;
        }

        return 1;
    }

    private function toLocalCarbon(
        DateTimeInterface|string|null $value,
        DateTimeZone $timezone,
    ): ?CarbonImmutable {
        if ($this->isEmptyDate($value)) {
            return null;
        }

        if ($value instanceof DateTimeInterface) {
            return CarbonImmutable::instance($value)
                ->setTimezone($timezone);
        }

        return CarbonImmutable::parse($value, $timezone);
    }

    private function isEmptyDate(mixed $value): bool
    {
        return $value === null
            || $value === ''
            || $value === '0000-00-00 00:00:00';
    }
}
