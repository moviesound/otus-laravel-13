<?php

namespace App\Services\Bot\Contexts;

use App\Contracts\Bot\Repositories\EventRepositoryInterface;
use App\Contracts\Bot\Repositories\RemindersRepository\SearchRemindersRepositoryInterface;
use App\Contracts\Bot\Repositories\TagsRepositoryInterface;
use App\Contracts\Bot\Repositories\TaskRepositoryInterface;
use App\Contracts\Bot\Repositories\UserRepositoryInterface;
use App\DTO\Bot\Models\ReminderQueueDTO;

final class ReminderContextBuilder
{
    public function __construct(
        private readonly SearchRemindersRepositoryInterface $reminderRepository,
        private readonly TaskRepositoryInterface $taskRepository,
        private readonly EventRepositoryInterface $eventRepository,
        private readonly TagsRepositoryInterface $tagRepository,
        private readonly UserRepositoryInterface $userRepository,
    ) {}

    public function build(ReminderQueueDTO $queue): ReminderContext
    {
        // 1. Напоминание
        $reminder = $this->reminderRepository->getReminderById($queue->reminder_id);

        // 2. Шаблон напоминания
        $reminderTemplate = $this->reminderRepository->getReminderTemplateById(
            $reminder->template_id
        );

        // 3. Пользователь
        $user = $this->userRepository->getUserAndSocialsById(
            $reminderTemplate->user_id
        );

        // 4. Сущность
        if ($reminderTemplate->entity_type === 'task') {
            $entityTemplate = $this->taskRepository->getTemplate(
                $reminderTemplate->entity_id
            );

            $entity = $this->taskRepository->getTask(
                $reminderTemplate->entity_id
            );

            $tags = $this->tagRepository->getTaskTags(
                $reminderTemplate->entity_id
            );
        } else {
            $entityTemplate = $this->eventRepository->getTemplate(
                $reminderTemplate->entity_id
            );

            $entity = $this->eventRepository->getEvent(
                $reminderTemplate->entity_id
            );

            $tags = $this->tagRepository->getEventTags(
                $reminderTemplate->entity_id
            );
        }

        return new ReminderContext(
            queue: $queue,
            reminder: $reminder,
            reminderTemplate: $reminderTemplate,
            entityTemplate: $entityTemplate,
            entity: $entity,
            tags: $tags,
            user: $user,
        );
    }
}
