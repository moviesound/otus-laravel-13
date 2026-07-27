<?php

namespace App\Repositories\Bot\PlanningRepository;

use App\Contracts\Bot\Repositories\PlanningRepository\CreationPlanningRepositoryInterface;
use App\DTO\Bot\Mappers\PlanningMapper;
use App\DTO\Bot\Models\EventDTO;
use App\DTO\Bot\Models\EventTemplateDTO;
use App\DTO\Bot\Models\TaskDTO;
use App\DTO\Bot\Models\TaskTemplateDTO;
use App\Models\Bot\Event;
use App\Models\Bot\EventTemplate;
use App\Models\Bot\Task;
use App\Models\Bot\TaskTemplate;

class CreationPlanningRepository implements CreationPlanningRepositoryInterface
{
    public function __construct(
        private TaskTemplate $taskTemplate,
        private EventTemplate $eventTemplate,
        private Task $task,
        private Event $event,
        private PlanningMapper $planningMapper
    ) {}

    public function createTaskTemplate(array $data): TaskTemplateDTO
    {
        $model = $this->taskTemplate->create([
            'user_id' => $data['user_id'],
            'title' => $data['title'],
            'description' => $data['description'] ?? null,

            'repeat_type' => $data['repeat_type'] ?? 'none',
            'repeat_interval' => $data['repeat_interval'] ?? null,

            'week_days' => $data['week_days'] ?? null,
            'weekly_common_time' => $data['weekly_common_time'] ?? null,
            'weekly_different_time' => $data['weekly_different_time'] ?? null,

            'month_days' => $data['month_days'] ?? null,
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

            'month_start' => $data['month_start'] ?? null,
            'day_start' => $data['day_start'] ?? null,
            'hour_start' => $data['hour_start'] ?? null,
            'minute_start' => $data['minute_start'] ?? null,

            'month_end' => $data['month_end'] ?? null,
            'day_end' => $data['day_end'] ?? null,
            'hour_end' => $data['hour_end'] ?? null,
            'minute_end' => $data['minute_end'] ?? null,

            'time_set_by_user' => (int) ($data['time_set_by_user'] ?? 0),

            'date_mode' => $data['date_mode'] ?? null,

            'period_start' => $data['period_start'] ?? null,
            'period_end' => $data['period_end'] ?? null,
            'deadline' => $data['deadline'] ?? null,

            'task_type' => $data['task_type'] ?? null,

            'status' => $data['status'] ?? 1,

            'has_call' => $data['has_call'] ?? 0,
            'has_sms' => $data['has_sms'] ?? 0,
        ]);

        return $this->planningMapper::taskTemplateFromModel($model);
    }

    public function createTask(array $data): TaskDTO
    {
        $model = $this->task->create([
            'template_id' => $data['template_id'],

            'period_start' => $data['period_start'] ?? null,
            'period_end' => $data['period_end'] ?? null,
            'deadline' => $data['deadline'] ?? null,

            'status' => $data['status'] ?? 'pending',

            'check_remind_next_time' => $data['check_remind_next_time'] ?? null,
            'next_system_remind_at' => $data['next_system_remind_at'] ?? null,
            'last_shown_in_digest_at' => $data['last_shown_in_digest_at'] ?? null,
        ]);

        return $this->planningMapper::taskFromModel($model);
    }

    public function createEventTemplate(array $data): EventTemplateDTO
    {
        $model = $this->eventTemplate->create([
            'user_id' => $data['user_id'] ?? null,
            'title' => $data['title'],
            'description' => $data['description'] ?? null,

            'repeat_type' => $data['repeat_type'] ?? 'none',
            'repeat_interval' => $data['repeat_interval'] ?? null,

            'week_days' => $data['week_days'] ?? null,
            'weekly_common_time' => $data['weekly_common_time'] ?? null,
            'weekly_different_time' => $data['weekly_different_time'] ?? null,

            'month_days' => $data['month_days'] ?? null,
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

            'month_start' => $data['month_start'] ?? null,
            'day_start' => $data['day_start'] ?? null,
            'hour_start' => $data['hour_start'] ?? null,
            'minute_start' => $data['minute_start'] ?? null,

            'month_end' => $data['month_end'] ?? null,
            'day_end' => $data['day_end'] ?? null,
            'hour_end' => $data['hour_end'] ?? null,
            'minute_end' => $data['minute_end'] ?? null,

            'time_set_by_user' => (int) ($data['time_set_by_user'] ?? 0),

            'date_mode' => $data['date_mode'] ?? 'deadline',

            'period_start' => $data['period_start'] ?? null,
            'period_end' => $data['period_end'] ?? null,
            'deadline' => $data['deadline'] ?? null,

            'event_type' => $data['event_type'] ?? null,

            'status' => $data['status'] ?? 1,

            'has_call' => $data['has_call'] ?? 0,
            'has_sms' => $data['has_sms'] ?? 0,
        ]);

        return $this->planningMapper::eventTemplateFromModel($model);
    }

    public function createEvent(array $data): EventDTO
    {
        $model = $this->event->create([
            'template_id' => $data['template_id'] ?? null,

            'period_start' => $data['period_start'] ?? null,
            'period_end' => $data['period_end'] ?? null,
            'deadline' => $data['deadline'] ?? null,

            'status' => $data['status'] ?? 'pending',

            'check_remind_next_time' => $data['check_remind_next_time'] ?? null,
            'next_system_remind_at' => $data['next_system_remind_at'] ?? null,
            'last_shown_in_digest_at' => $data['last_shown_in_digest_at'] ?? null,
        ]);

        return $this->planningMapper::eventFromModel($model);
    }
}
