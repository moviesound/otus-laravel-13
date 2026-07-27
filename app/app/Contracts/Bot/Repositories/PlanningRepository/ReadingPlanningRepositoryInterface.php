<?php

namespace App\Contracts\Bot\Repositories\PlanningRepository;

use App\DTO\Bot\Models\EventDTO;
use App\DTO\Bot\Models\EventTemplateDTO;
use App\DTO\Bot\Models\TaskDTO;
use App\DTO\Bot\Models\TaskTemplateDTO;
use Carbon\CarbonInterface;

interface ReadingPlanningRepositoryInterface
{
    public function getTaskTemplateById(
        int $userId,
        int $templateId
    ): ?TaskTemplateDTO;

    public function getEventTemplateById(
        int $userId,
        int $templateId
    ): ?EventTemplateDTO;

    public function getLastTaskByTemplateId(
        int $userId,
        int $templateId
    ): ?TaskDTO;

    public function getLastEventByTemplateId(
        int $userId,
        int $templateId
    ): ?EventDTO;

    public function getTasksForToday(
        int             $userId,
        CarbonInterface $from,
        CarbonInterface $to
    ): array;

    public function countUsersTasksEvents(int $userId): int;
}

