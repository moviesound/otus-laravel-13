<?php

namespace App\Contracts\Bot\Repositories\PlanningRepository;

interface UpdatingPlanningRepositoryInterface
{
    public function updateTaskTemplate(
        int $id,
        array $data
    ): bool;

    public function updateEventTemplate(
        int $id,
        array $data
    ): bool;

    public function completeTask(
        int $userId,
        int $taskTemplateId
    ): bool;

    public function completeEvent(
        int $userId,
        int $eventTemplateId
    ): bool;

    public function markTaskOverdue(
        int $templateId
    ): bool;
}
