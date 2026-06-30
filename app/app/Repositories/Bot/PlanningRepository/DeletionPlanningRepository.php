<?php

namespace App\Repositories\Bot\PlanningRepository;

use App\Contracts\Bot\Repositories\PlanningRepository\DeletionPlanningRepositoryInterface;
use App\Models\Bot\Task;
use App\Models\Bot\TaskTemplate;
use App\Models\Bot\CommonEntity;
use Illuminate\Support\Facades\DB;
use App\Models\Bot\EventTemplate;
use App\Models\Bot\Event;
use App\Models\Bot\ReminderTemplate;

class DeletionPlanningRepository implements DeletionPlanningRepositoryInterface
{
    public function removeTasksByTemplateId(int $userId, int $templateId, bool $deleteTemplate = true): bool
    {
        return DB::transaction(function () use ($userId, $templateId, $deleteTemplate) {

            $template = TaskTemplate::query()
                ->where('id', $templateId)
                ->where('user_id', $userId)
                ->first();

            if (!$template) {
                return false;
            }

            // 1. delete tasks
            $template->tasks()->delete();

            // 2. detach tags (pivot)
            $template->tags()->detach();

            // 3. detach from common entities (ВАЖНО — твоя новая схема)
            $template->entities()->detach();

            // 4. delete reminder templates (morph)
            $template->reminders()->delete();

            // 5. delete template
            if ($deleteTemplate) {
                $template->delete();
            }

            return true;
        });
    }

    public function removeEventsByTemplateId(int $userId, int $templateId, bool $deleteTemplate = true): bool
    {
        return DB::transaction(function () use ($userId, $templateId, $deleteTemplate) {

            $template = EventTemplate::query()
                ->where('id', $templateId)
                ->where('user_id', $userId)
                ->first();

            if (!$template) {
                return false;
            }

            // 1. delete events
            $template->events()->delete();

            // 2. detach tags
            $template->tags()->detach();

            // 3. detach common entity links
            $template->entities()->detach();

            // 4. delete reminder templates
            $template->reminders()->delete();

            // 5. delete template itself
            if ($deleteTemplate) {
                $template->delete();
            }

            return true;
        });
    }

    public function removeReminderTemplate(int $templateId): void
    {
        ReminderTemplate::query()
            ->where('id', $templateId)
            ->with('reminders.reminderQueues')
            ->get()
            ->each(function (ReminderTemplate $tpl) {

                // queues -> reminders
                $tpl->reminders()->each(function ($reminder) {
                    $reminder->reminderQueues()->delete();
                    $reminder->delete();
                });

                $tpl->delete();
            });
    }

    public function removeCommonEntity(int $entityId, string $type, int $childId): void
    {
        $entity = CommonEntity::query()->find($entityId);

        if (!$entity) {
            return;
        }

        $entity->detach($type, $childId);
    }
}
