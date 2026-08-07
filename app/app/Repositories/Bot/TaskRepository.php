<?php

namespace App\Repositories\Bot;

use App\Contracts\Bot\Repositories\TaskRepositoryInterface;
use App\DTO\Bot\Mappers\TaskMapper;
use App\DTO\Bot\Models\TaskDTO;
use App\DTO\Bot\Models\TaskTemplateDTO;
use App\Models\Bot\Task;
use App\Models\Bot\TaskTemplate;

class TaskRepository implements TaskRepositoryInterface
{
    public function getTask(int $templateId): TaskDTO
    {
        $task = Task::query()
            ->byTaskTplId($templateId)
            ->firstOrFail();

        return TaskMapper::taskFromModel($task);
    }


    public function getTemplate(int $templateId): TaskTemplateDTO
    {
        $template = TaskTemplate::query()
            ->findOrFail($templateId);

        return TaskMapper::templateFromModel($template);
    }
}
