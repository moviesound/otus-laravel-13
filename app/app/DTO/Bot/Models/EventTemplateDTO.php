<?php

namespace App\DTO\Bot\Models;

class EventTemplateDTO
{
    public function __construct(
        public int $id,
        public ?int $user_id,
        public string $title,
        public ?string $description = null,

        public string $repeat_type = 'none',
        public ?int $repeat_interval = null,

        public ?array $week_days = null,
        public ?string $weekly_common_time = null,
        public ?string $weekly_different_time = null,

        public ?string $month_days = null,
        public ?string $monthly_common_time = null,
        public ?string $monthly_different_time = null,

        public ?string $quarter_type = null,
        public ?int $month_in_quarter = null,
        public ?string $day_in_quarter = null,

        public ?string $start_month_in_quarter = null,
        public ?string $start_day_in_quarter = null,
        public ?string $end_month_in_quarter = null,
        public ?string $end_day_in_quarter = null,

        public ?string $year_type = null,
        public ?int $month_in_year = null,
        public ?string $day_in_year = null,

        public ?string $start_month_in_year = null,
        public ?string $start_day_in_year = null,
        public ?string $end_month_in_year = null,
        public ?string $end_day_in_year = null,

        public ?int $month_start = null,
        public ?int $day_start = null,
        public ?int $hour_start = null,
        public ?int $minute_start = null,

        public ?int $month_end = null,
        public ?int $day_end = null,
        public ?int $hour_end = null,
        public ?int $minute_end = null,

        public string $date_mode = 'deadline',

        public ?string $period_start = null,
        public ?string $period_end = null,
        public ?string $deadline = null,

        public int $time_set_by_user = 0,

        public ?string $event_type = null,

        public int $status = 2,

        public int $has_call = 0,
        public int $has_sms = 0,
    ) {}
}
