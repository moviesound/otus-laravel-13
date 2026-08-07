<?php

namespace App\DTO\Bot\Models;

final class ReminderQueueDTO
{
    public function __construct(
        public int $id,

        public ?int $reminder_id = null,

        public ?int $user_id = null,

        public ?string $channel = null,

        public string $status = 'pending',

        public int $sent_times = 0,

        public ?string $last_sent_at = null,

        public ?string $date_remind = null,

        public ?string $process_name = null,

        public ?string $locked_by = null,

        public ?string $locked_at = null,

        public ?string $created_at = null,
        public ?string $updated_at = null,
    ) {}
}
