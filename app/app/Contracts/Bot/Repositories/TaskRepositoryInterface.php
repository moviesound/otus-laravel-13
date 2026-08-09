<?php

namespace App\Contracts\Bot\Repositories;

use App\DTO\Bot\Models\TaskDTO;
use App\DTO\Bot\Models\TaskTemplateDTO;

interface TaskRepositoryInterface
{
    public function getTask(int $templateId): TaskDTO;
    public function getTemplate(int $templateId): TaskTemplateDTO;
}
