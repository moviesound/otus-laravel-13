<?php

namespace App\Services\Bot\Entities;

use App\DTO\Bot\Scenarios\Planning\DateResultDTO;
use App\DTO\Bot\User\UserDefaultTimeDTO;
use App\DTO\Bot\User\UserDTO;
use App\Models\Bot\CommonEntity;
use App\Models\Bot\EventTemplate;
use App\Models\Bot\Reminder;
use App\Models\Bot\ReminderQueue;
use App\Models\Bot\Tag;
use App\Models\Bot\TaskTemplate;
use App\Services\Bot\Helpers\Dates\Planning\PlanningDateInputFactory;
use App\Services\Bot\Helpers\Dates\Planning\PlanningDatesResolver;
use App\Services\Bot\Helpers\Dates\Planning\SystemTaskReminderCalculator;
use Carbon\CarbonImmutable;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

final class PlanningEntityCreator
{
    public function __construct(
        private readonly PlanningDatesResolver $datesResolver,
        private readonly PlanningDateInputFactory $dateInputFactory,
        private readonly SystemTaskReminderCalculator $systemTaskReminderCalculator,
    ) {
    }

    public function create(
        UserDTO $user,
        array $data,
        string $channel,
    ): CommonEntity {
        return DB::transaction(function () use ($user, $data, $channel) {
            $entity = $this->createCommonEntity();

            match ($data['type']) {
                'task' => $this->createTask(
                    entity: $entity,
                    user: $user,
                    data: $data,
                    channel: $channel,
                ),

                'event' => $this->createEvent(
                    entity: $entity,
                    user: $user,
                    data: $data,
                    channel: $channel,
                ),

                default => throw new InvalidArgumentException(
                    "Unknown entity type {$data['type']}"
                ),
            };

            return $entity;
        });
    }

    private function createCommonEntity(): CommonEntity
    {
        $entity = new CommonEntity();
        $entity->save();
        return $entity;
    }

    private function createTask(
        CommonEntity $entity,
        UserDTO $user,
        array $data,
        string $channel,
    ): void {
        $template = $this->createTaskTemplate($user, $data);

        $entity->attach('task', $template->id);

        $this->createTaskEntity(
            user: $user,
            template: $template,
            data: $data,
        );

        $this->createReminders(
            user: $user,
            template: $template,
            entityType: 'task',
            data: $data,
            channel: $channel,
        );

        $this->attachTags(
            relation: $template->tags(),
            user: $user,
            tags: $data['tags'] ?? null,
        );
    }

    private function createEvent(
        CommonEntity $entity,
        UserDTO $user,
        array $data,
        string $channel,
    ): void {
        $template = $this->createEventTemplate($user, $data);

        $entity->attach('event', $template->id);

        $this->createEventEntity(
            user: $user,
            template: $template,
            data: $data,
        );

        $this->createReminders(
            user: $user,
            template: $template,
            entityType: 'event',
            data: $data,
            channel: $channel,
        );

        $this->attachTags(
            relation: $template->tags(),
            user: $user,
            tags: $data['tags'] ?? null,
        );
    }

    /*
    |--------------------------------------------------------------------------
    | TASK
    |--------------------------------------------------------------------------
    */

    private function createTaskTemplate(
        UserDTO $user,
        array $data,
    ): TaskTemplate {
        $dateStart = $this->parseDateString($data['date_start'] ?? null);
        $dateEnd = $this->parseDateString($data['date_end'] ?? null);

        $input = $this->dateInputFactory->fromUserAndData($user, $data);
        $dates = $this->datesResolver->resolve($input);

        return TaskTemplate::create([
            'user_id' => $user->id,

            'title' => $data['title'],
            'description' => $data['description'] ?? null,

            'repeat_type' => $data['repeat_type'] ?? 'none',
            'repeat_interval' => $data['repeat_interval'] ?? null,

            'week_days' => $this->normalizeCommaValue($data['week_days'] ?? null),

            'weekly_common_time' => $data['weekly_common_time'] ?? null,
            'weekly_different_time' => $data['weekly_different_time'] ?? null,

            'month_days' => $this->normalizeCommaValue($data['month_days'] ?? null),

            'monthly_common_time' => $data['monthly_common_time'] ?? null,
            'monthly_different_time' => $data['monthly_different_time'] ?? null,

            'quarter_type' => $data['quarter_type'] ?? null,
            'month_in_quarter' => $data['month_in_quarter'] ?? null,
            'day_in_quarter' => $data['day_in_quarter'] ?? null,
            'start_month_in_quarter' => $data['start_month_in_quarter'] ?? null,
            'start_day_in_quarter' => $data['start_day_in_quarter'] ?? null,
            'end_month_in_quarter' => $data['end_month_in_quarter'] ?? null,
            'end_day_in_quarter' => $data['end_day_in_quarter'] ?? null,

            'year_type' => $data['year_type'] ?? null,
            'month_in_year' => $data['month_in_year'] ?? null,
            'day_in_year' => $data['day_in_year'] ?? null,
            'start_month_in_year' => $data['start_month_in_year'] ?? null,
            'start_day_in_year' => $data['start_day_in_year'] ?? null,
            'end_month_in_year' => $data['end_month_in_year'] ?? null,
            'end_day_in_year' => $data['end_day_in_year'] ?? null,

            'month_start' => $dateStart['month'] ?? null,
            'day_start' => $dateStart['day'] ?? null,
            'hour_start' => $dateStart['hour'] ?? null,
            'minute_start' => $dateStart['minute'] ?? null,

            'month_end' => $dateEnd['month'] ?? null,
            'day_end' => $dateEnd['day'] ?? null,
            'hour_end' => $dateEnd['hour'] ?? null,
            'minute_end' => $dateEnd['minute'] ?? null,

            'time_set_by_user' => (int) $this->getUserTimeFlag($data),

            'date_mode' => $data['date_mode'] ?? null,

            'period_start' => $dates->periodStart ?? null,
            'period_end' => $dates->periodEnd ?? null,
            'deadline' => $dates->deadline ?? null,

            'task_type' => $data['subType'],
            'status' => 1,

            'has_call' => !empty($data['has_call']) ? 1 : 0,
            'has_sms' => !empty($data['has_sms']) ? 1 : 0,
        ]);
    }

    private function createTaskEntity(
        UserDTO $user,
        TaskTemplate $template,
        array $data,
    ): void {
        $input = $this->dateInputFactory->fromUserAndData($user, $data);
        $dates = $this->datesResolver->resolve($input);

        $nextSystemRemindAt = null;

        if (empty($data['reminders'])) {
            $nextSystemRemindAt = $this->calculateNextSystemTaskRemindAt(
                user: $user,
                data: $data,
                dates: $dates,
            );
        }

        $template->tasks()->create([
            'status' => 'pending',
            'period_start' => $dates->periodStart ?? null,
            'period_end' => $dates->periodEnd ?? null,
            'deadline' => $dates->deadline ?? null,
            'next_system_remind_at' => $nextSystemRemindAt,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | EVENT
    |--------------------------------------------------------------------------
    */

    private function createEventTemplate(
        UserDTO $user,
        array $data,
    ): EventTemplate {
        $dateStart = $this->parseDateString($data['date_start'] ?? null);
        $dateEnd = $this->parseDateString($data['date_end'] ?? null);

        $input = $this->dateInputFactory->fromUserAndData($user, $data);
        $dates = $this->datesResolver->resolve($input);

        return EventTemplate::create([
            'user_id' => $user->id,

            'title' => $data['title'],
            'description' => $data['description'] ?? null,

            'repeat_type' => $data['repeat_type'] ?? 'none',
            'repeat_interval' => $data['repeat_interval'] ?? null,

            'week_days' => $this->normalizeCommaValue($data['week_days'] ?? null),

            'weekly_common_time' => $data['weekly_common_time'] ?? null,
            'weekly_different_time' => $data['weekly_different_time'] ?? null,

            'month_days' => $this->normalizeCommaValue($data['month_days'] ?? null),

            'monthly_common_time' => $data['monthly_common_time'] ?? null,
            'monthly_different_time' => $data['monthly_different_time'] ?? null,

            'quarter_type' => $data['quarter_type'] ?? null,
            'month_in_quarter' => $data['month_in_quarter'] ?? null,
            'day_in_quarter' => $data['day_in_quarter'] ?? null,
            'start_month_in_quarter' => $data['start_month_in_quarter'] ?? null,
            'start_day_in_quarter' => $data['start_day_in_quarter'] ?? null,
            'end_month_in_quarter' => $data['end_month_in_quarter'] ?? null,
            'end_day_in_quarter' => $data['end_day_in_quarter'] ?? null,

            'year_type' => $data['year_type'] ?? null,
            'month_in_year' => $data['month_in_year'] ?? null,
            'day_in_year' => $data['day_in_year'] ?? null,
            'start_month_in_year' => $data['start_month_in_year'] ?? null,
            'start_day_in_year' => $data['start_day_in_year'] ?? null,
            'end_month_in_year' => $data['end_month_in_year'] ?? null,
            'end_day_in_year' => $data['end_day_in_year'] ?? null,

            'month_start' => $dateStart['month'] ?? null,
            'day_start' => $dateStart['day'] ?? null,
            'hour_start' => $dateStart['hour'] ?? null,
            'minute_start' => $dateStart['minute'] ?? null,

            'month_end' => $dateEnd['month'] ?? null,
            'day_end' => $dateEnd['day'] ?? null,
            'hour_end' => $dateEnd['hour'] ?? null,
            'minute_end' => $dateEnd['minute'] ?? null,

            'time_set_by_user' => (int) $this->getUserTimeFlag($data),

            'date_mode' => $data['date_mode'] ?? null,

            'period_start' => $dates->periodStart ?? null,
            'period_end' => $dates->periodEnd ?? null,
            'deadline' => $dates->deadline ?? null,

            'event_type' => $data['subType'],
            'status' => 1,

            'has_call' => !empty($data['has_call']) ? 1 : 0,
            'has_sms' => !empty($data['has_sms']) ? 1 : 0,
        ]);
    }

    private function createEventEntity(
        UserDTO $user,
        EventTemplate $template,
        array $data,
    ): void {
        $input = $this->dateInputFactory->fromUserAndData($user, $data);
        $dates = $this->datesResolver->resolve($input);

        $template->events()->create([
            'status' => 'pending',
            'period_start' => $dates->periodStart ?? null,
            'period_end' => $dates->periodEnd ?? null,
            'deadline' => $dates->deadline ?? null,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | REMINDERS
    |--------------------------------------------------------------------------
    */

    private function createReminders(
        UserDTO $user,
        TaskTemplate|EventTemplate $template,
        string $entityType,
        array $data,
        string $channel,
    ): void {
        if (empty($data['reminders']) || !is_array($data['reminders'])) {
            return;
        }

        $input = $this->dateInputFactory->fromUserAndData($user, $data);
        $dates = $this->datesResolver->resolve($input);

        $entityTimeLocal = $this->dateToString(
            $dates->deadline ?? $dates->periodStart ?? null
        );

        if ($entityTimeLocal === null) {
            return;
        }

        foreach ($data['reminders'] as $reminderData) {
            $remindLocalDate = $this->calculateReminderLocalDate(
                entityTimeLocal: $entityTimeLocal,
                type: $reminderData['type'],
                value: (int) $reminderData['value'],
            );

            $reminderTemplate = $template->reminders()->create([
                'user_id' => $user->id,
                'text' => $reminderData['text'] ?? null,
                'remind_type' => $reminderData['type'],
                'remind_value' => (int) $reminderData['value'],
                'is_sub_task' => 0,
                'entity_type' => $entityType,
                'entity_id' => $template->id,
                'has_call' => !empty($reminderData['has_call']) ? 1 : 0,
                'has_sms' => !empty($reminderData['has_sms']) ? 1 : 0,
                'created_at' => now(),
            ]);

            $utcRemindDate = $this->userLocalToUtc(
                localDate: $remindLocalDate,
                timezone: $this->userValue($user, 'timezone', 'UTC'),
            );

            $reminder = Reminder::create([
                'template_id' => $reminderTemplate->id,
                'date_remind' => $utcRemindDate,
                'status' => 'pending',
            ]);

            ReminderQueue::create([
                'entity_id' => $template->id,
                'entity_type' => $entityType,
                'reminder_id' => $reminder->id,
                'user_id' => $user->id,
                'channel' => $channel,
                'status' => 'pending',
                'date_remind' => $utcRemindDate,
            ]);
        }
    }

    private function calculateReminderLocalDate(
        string $entityTimeLocal,
        string $type,
        int $value,
    ): string {
        $date = CarbonImmutable::parse($entityTimeLocal);

        return match ($type) {
            'hours' => $date->subHours($value)->format('Y-m-d H:i:s'),
            'days' => $date->subDays($value)->format('Y-m-d H:i:s'),
            'weeks' => $date->subWeeks($value)->format('Y-m-d H:i:s'),
            'months' => $date->subMonthsNoOverflow($value)->format('Y-m-d H:i:s'),
            default => $date->format('Y-m-d H:i:s'),
        };
    }

    /*
    |--------------------------------------------------------------------------
    | TAGS
    |--------------------------------------------------------------------------
    */

    private function attachTags(
        BelongsToMany $relation,
        UserDTO $user,
        string|array|null $tags,
    ): void {
        if (empty($tags)) {
            return;
        }

        $items = $this->parseTags($tags);

        if ($items === []) {
            return;
        }

        foreach ($items as $tagName) {
            $tag = Tag::query()->firstOrCreate(
                [
                    'user_id' => $user->id,
                    'tag' => $tagName,
                ],
                [
                    'count' => 0,
                    'created_at' => now(),
                ]
            );

            $alreadyAttached = $relation
                ->where('tags.id', $tag->id)
                ->exists();

            if (!$alreadyAttached) {
                $relation->attach($tag->id, [
                    'created_at' => now(),
                ]);

                $tag->increaseCount();
            }
        }
    }

    private function parseTags(string|array $tags): array
    {
        $value = is_array($tags) ? implode(',', $tags) : $tags;

        return collect(explode(',', $value))
            ->map(fn (string $tag) => trim($tag))
            ->filter()
            ->map(fn (string $tag) => ltrim($tag, '#'))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    /*
    |--------------------------------------------------------------------------
    | DATES / HELPERS
    |--------------------------------------------------------------------------
    */

    private function parseDateString(?string $date): ?array
    {
        if (empty($date)) {
            return null;
        }

        $parts = explode(' ', $date);

        if (count($parts) !== 2) {
            return null;
        }

        $dateParts = explode('.', $parts[0]);
        $timeParts = explode(':', $parts[1]);

        if (count($dateParts) !== 2 || count($timeParts) !== 2) {
            return null;
        }

        return [
            'day' => (int) $dateParts[0],
            'month' => (int) $dateParts[1],
            'hour' => (int) $timeParts[0],
            'minute' => (int) $timeParts[1],
        ];
    }

    private function getUserTimeFlag(array $data): bool
    {
        return match ($data['repeat_type'] ?? 'none') {
            'none', 'daily' => $this->hasDateUserTime($data),

            'weekly' => !empty($data['week_days'])
                && (
                    !empty($data['weekly_common_time'])
                    || !empty($data['weekly_different_time'])
                ),

            'monthly', 'quarterly', 'yearly' => false,

            default => false,
        };
    }

    private function hasDateUserTime(array $data): bool
    {
        if (empty($data['date_mode'])) {
            return false;
        }

        if ($data['date_mode'] === 'deadline') {
            return !empty($data['deadline_date']['hour'])
                || !empty($data['deadline_date']['minute']);
        }

        return !empty($data['period_start']['hour'])
            || !empty($data['period_start']['minute'])
            || !empty($data['period_end']['hour'])
            || !empty($data['period_end']['minute']);
    }

    private function normalizeCommaValue(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_array($value)) {
            return implode(',', $value);
        }

        return (string) $value;
    }

    private function dateToString(mixed $date): ?string
    {
        if ($date === null) {
            return null;
        }

        if ($date instanceof DateTimeInterface) {
            return $date->format('Y-m-d H:i:s');
        }

        if (is_string($date)) {
            return $date;
        }

        return null;
    }

    private function userLocalToUtc(
        string $localDate,
        string $timezone,
    ): string {
        return CarbonImmutable::parse($localDate, $timezone)
            ->utc()
            ->format('Y-m-d H:i:s');
    }

    private function userValue(
        UserDTO $user,
        string $key,
        mixed $default = null,
    ): mixed {
        return $user->{$key}
            ?? $default;
    }

    private function calculateNextSystemTaskRemindAt(
        UserDTO $user,
        array $data,
        DateResultDTO $dates,
    ): ?DateTimeInterface {
        /**
         * Системные напоминания только,
         * если пользовательские reminders НЕ заданы.
         */
        if (!empty($data['reminders'])) {
            return null;
        }

        $timezone = $this->userValue($user, 'timezone', 'UTC');

        $allowedTime = new UserDefaultTimeDTO(
            morningWorkdays: $this->userValue($user, 'morning_time_workdays', '08:00'),
            morningHolidays: $this->userValue($user, 'morning_time_holidays', '10:00'),
            eveningWorkdays: $this->userValue($user, 'evening_time_workdays', '18:00'),
            eveningHolidays: $this->userValue($user, 'evening_time_holidays', '18:00'),
        );

        $deadlineLocal = null;
        $baseLocal = null;


        if (
            ($data['date_mode'] ?? null) === 'period'
            && $dates->periodStart !== null
        ) {
            $baseLocal = $dates->periodStart;
            $deadlineLocal = $dates->periodEnd;
        } elseif ($dates->deadline !== null) {
            $deadlineLocal = $dates->deadline;
        }

        if ($deadlineLocal === null) {
            return null;
        }

        return $this->systemTaskReminderCalculator->calculate(
            deadlineLocal: $deadlineLocal,
            baseLocal: $baseLocal,
            timezone: $timezone,
            allowedTime: $allowedTime,
        );
    }
}
