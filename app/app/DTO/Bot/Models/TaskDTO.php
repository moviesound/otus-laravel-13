<?php

namespace App\DTO\Bot\Models;

class TaskDTO
{
    public function __construct(
        public int $id,
        public ?int $template_id = null,

        public ?string $period_start = null,
        public ?string $period_end = null,
        public ?string $deadline = null,

        public string $status = 'pending',

        public ?string $check_remind_next_time = null,
        public ?string $next_system_remind_at = null,
        public ?string $last_shown_in_digest_at = null,

        public ?string $created_at = null,
    ) {}
}
