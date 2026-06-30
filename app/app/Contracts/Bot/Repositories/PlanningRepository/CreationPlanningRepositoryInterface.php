<?php

namespace App\Contracts\Bot\Repositories\PlanningRepository;

use App\DTO\Bot\Models\TaskTemplateDTO;
use App\DTO\Bot\Models\EventTemplateDTO;
use App\DTO\Bot\Models\EventDTO;
use App\DTO\Bot\Models\TaskDTO;

interface CreationPlanningRepositoryInterface
{
    public function createTaskTemplate(array $data): TaskTemplateDTO;
    public function createTask(array $data): TaskDTO;

    public function createEventTemplate(array $data): EventTemplateDTO;

    public function createEvent(array $data): EventDTO;
}
