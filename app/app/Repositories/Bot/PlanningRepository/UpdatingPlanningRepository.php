<?php

namespace App\Repositories\Bot\PlanningRepository;

use App\Contracts\Bot\Repositories\PlanningRepository\UpdatingPlanningRepositoryInterface;
use App\Models\Bot\EventTemplate;
use App\Models\Bot\Task;
use App\Models\Bot\TaskTemplate;

class UpdatingPlanningRepository implements UpdatingPlanningRepositoryInterface
{
    public function updateTaskTemplate(
        int   $id,
        array $data
    ): bool
    {
        TaskTemplate::query()
            ->whereKey($id)
            ->update($data);
        return true;
    }

    public function updateEventTemplate(
        int   $id,
        array $data
    ): bool
    {
        EventTemplate::query()
            ->whereKey($id)
            ->update($data);
        return true;
    }

    public function completeTask(
        int $userId,
        int $taskTemplateId
    ): bool
    {
        TaskTemplate::query()
            ->where('user_id', $userId)
            ->whereKey($taskTemplateId)
            ->update(['status' => 1]);
        return true;
    }

    public function completeEvent(
        int $userId,
        int $eventTemplateId
    ): bool
    {
        EventTemplate::query()
            ->where('user_id', $userId)
            ->whereKey($eventTemplateId)
            ->update(['status' => 1]);
        return true;
    }

    public function markTaskOverdue(
        int $templateId
    ): bool
    {
        Task::query()
            ->where('template_id', $templateId)
            ->whereIn('status', ['pending', 'processing'])
            ->update([
                'status' => 'overdue',
            ]);
        return true;
    }
}
