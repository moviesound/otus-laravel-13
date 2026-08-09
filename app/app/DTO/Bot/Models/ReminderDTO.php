<?php

namespace App\DTO\Bot\Models;

final class ReminderDTO
{
    public function __construct(
        public int $id,

        public int $template_id,

        public ?string $date_remind = null,

        public ?string $status = 'pending',

        public ?string $created_at = null,
        public ?string $updated_at = null,
    ) {}
}
