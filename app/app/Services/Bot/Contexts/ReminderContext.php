<?php

namespace App\Services\Bot\Contexts;

use App\DTO\Bot\Models\EventDTO;
use App\DTO\Bot\Models\EventTemplateDTO;
use App\DTO\Bot\Models\ReminderDTO;
use App\DTO\Bot\Models\ReminderQueueDTO;
use App\DTO\Bot\Models\ReminderTemplateDTO;
use App\DTO\Bot\Models\TaskDTO;
use App\DTO\Bot\Models\TaskTemplateDTO;
use App\DTO\Bot\User\UserDTO;

final class ReminderContext
{
    public function __construct(
        public ReminderQueueDTO $queue,

        public ReminderDTO $reminder,

        public ReminderTemplateDTO $reminderTemplate,

        public TaskTemplateDTO|EventTemplateDTO $entityTemplate,

        public EventDTO|TaskDTO $entity,

        public array $tags,

        public UserDTO $user,
    ) {}
}
