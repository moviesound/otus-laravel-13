<?php

namespace App\DTO\Bot\Models;

final class ReminderTemplateDTO
{
    public function __construct(
        public int $id,

        public ?int $user_id = null,

        public ?string $text = null,

        public ?string $remind_type = null,
        public int $remind_value = 1,

        public int $is_sub_task = 0,

        public string $entity_type = 'task',
        public ?int $entity_id = null,

        public int $has_call = 0,
        public int $has_sms = 0,

        public ?string $created_at = null,
        public ?string $updated_at = null,
    ) {}
}
