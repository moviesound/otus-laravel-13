<?php

namespace App\Contracts\Bot\Repositories\PlanningRepository;

interface DeletionPlanningRepositoryInterface
{
    public function removeTasksByTemplateId(int $userId, int $templateId, bool $deleteTemplate = true): bool;
    public function removeEventsByTemplateId(int $userId, int $templateId, bool $deleteTemplate = true): bool;
    public function removeReminderTemplate(int $templateId): void;
    public function removeCommonEntity(int $entityId, string $type, int $childId): void;
}
