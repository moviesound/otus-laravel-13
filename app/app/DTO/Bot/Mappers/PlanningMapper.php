<?php

namespace App\DTO\Bot\Mappers;

use App\DTO\Bot\Models\TaskDTO;
use App\DTO\Bot\Models\TaskTemplateDTO;
use App\DTO\Bot\Models\EventDTO;
use App\DTO\Bot\Models\EventTemplateDTO;
use App\Models\Bot\Task;
use App\Models\Bot\TaskTemplate;
use App\Models\Bot\Event;
use App\Models\Bot\EventTemplate;

class PlanningMapper
{
    public static function taskTemplateFromModel(TaskTemplate $model): TaskTemplateDTO
    {
        return new TaskTemplateDTO(
            id: $model->id,
            user_id: $model->user_id,
            title: $model->title,
            description: $model->description,
            repeat_type: $model->repeat_type,
            repeat_interval: $model->repeat_interval,
            week_days: $model->week_days,
            weekly_common_time: $model->weekly_common_time,
            weekly_different_time: $model->weekly_different_time,
            month_days: $model->month_days,
            monthly_common_time: $model->monthly_common_time,
            monthly_different_time: $model->monthly_different_time,
            quarter_type: $model->quarter_type,
            month_in_quarter: $model->month_in_quarter,
            day_in_quarter: $model->day_in_quarter,
            start_month_in_quarter: $model->start_month_in_quarter,
            start_day_in_quarter: $model->start_day_in_quarter,
            end_month_in_quarter: $model->end_month_in_quarter,
            end_day_in_quarter: $model->end_day_in_quarter,
            year_type: $model->year_type,
            month_in_year: $model->month_in_year,
            day_in_year: $model->day_in_year,
            start_month_in_year: $model->start_month_in_year,
            start_day_in_year: $model->start_day_in_year,
            end_month_in_year: $model->end_month_in_year,
            end_day_in_year: $model->end_day_in_year,
            month_start: $model->month_start,
            day_start: $model->day_start,
            hour_start: $model->hour_start,
            minute_start: $model->minute_start,
            month_end: $model->month_end,
            day_end: $model->day_end,
            hour_end: $model->hour_end,
            minute_end: $model->minute_end,
            time_set_by_user: $model->time_set_by_user,
            date_mode: $model->date_mode,
            period_start: $model->period_start,
            period_end: $model->period_end,
            deadline: $model->deadline,
            task_type: $model->task_type,
            status: $model->status,
            has_call: $model->has_call,
            has_sms: $model->has_sms,
        );
    }

    public static function taskFromModel(Task $model): TaskDTO
    {
        return new TaskDTO(
            id: $model->id,
            template_id: $model->template_id,
            status: $model->status,
            period_start: $model->period_start,
            period_end: $model->period_end,
            deadline: $model->deadline,
            check_remind_next_time: $model->check_remind_next_time,
            next_system_remind_at: $model->next_system_remind_at,
            last_shown_in_digest_at: $model->last_shown_in_digest_at,
            created_at: $model->created_at,
        );
    }

    public static function eventTemplateFromModel(EventTemplate $model): EventTemplateDTO
    {
        return new EventTemplateDTO(
            id: $model->id,
            user_id: $model->user_id,
            title: $model->title,
            description: $model->description,
            repeat_type: $model->repeat_type,
            repeat_interval: $model->repeat_interval,
            week_days: $model->week_days,
            weekly_common_time: $model->weekly_common_time,
            weekly_different_time: $model->weekly_different_time,
            month_days: $model->month_days,
            monthly_common_time: $model->monthly_common_time,
            monthly_different_time: $model->monthly_different_time,
            quarter_type: $model->quarter_type,
            month_in_quarter: $model->month_in_quarter,
            day_in_quarter: $model->day_in_quarter,
            start_month_in_quarter: $model->start_month_in_quarter,
            start_day_in_quarter: $model->start_day_in_quarter,
            end_month_in_quarter: $model->end_month_in_quarter,
            end_day_in_quarter: $model->end_day_in_quarter,
            year_type: $model->year_type,
            month_in_year: $model->month_in_year,
            day_in_year: $model->day_in_year,
            start_month_in_year: $model->start_month_in_year,
            start_day_in_year: $model->start_day_in_year,
            end_month_in_year: $model->end_month_in_year,
            end_day_in_year: $model->end_day_in_year,
            month_start: $model->month_start,
            day_start: $model->day_start,
            hour_start: $model->hour_start,
            minute_start: $model->minute_start,
            month_end: $model->month_end,
            day_end: $model->day_end,
            hour_end: $model->hour_end,
            minute_end: $model->minute_end,
            time_set_by_user: $model->time_set_by_user,
            date_mode: $model->date_mode,
            period_start: $model->period_start,
            period_end: $model->period_end,
            deadline: $model->deadline,
            event_type: $model->event_type,
            status: $model->status,
            has_call: $model->has_call,
            has_sms: $model->has_sms,
        );
    }

    public static function eventFromModel(Event $model): EventDTO
    {
        return new EventDTO(
            id: $model->id,
            template_id: $model->template_id,
            status: $model->status,
            period_start: $model->period_start,
            period_end: $model->period_end,
            deadline: $model->deadline,
            check_remind_next_time: $model->check_remind_next_time,
            next_system_remind_at: $model->next_system_remind_at,
            last_shown_in_digest_at: $model->last_shown_in_digest_at,
            created_at: $model->created_at,
        );
    }
}
