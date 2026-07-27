<?php

namespace App\Repositories\Bot\PlanningRepository;

use App\Contracts\Bot\Repositories\PlanningRepository\ReadingPlanningRepositoryInterface;
use App\DTO\Bot\Mappers\PlanningMapper;
use App\DTO\Bot\Models\EventDTO;
use App\DTO\Bot\Models\EventTemplateDTO;
use App\DTO\Bot\Models\TaskDTO;
use App\DTO\Bot\Models\TaskTemplateDTO;
use App\Models\Bot\Event;
use App\Models\Bot\EventTemplate;
use App\Models\Bot\Task;
use App\Models\Bot\TaskTemplate;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;

class ReadingPlanningRepository implements ReadingPlanningRepositoryInterface
{
    public function __construct(
        private TaskTemplate $taskTemplate,
        private EventTemplate $eventTemplate,
        private Task $task,
        private Event $event,
        private PlanningMapper $planningMapper,
    ) {}

    public function getTaskTemplateById(
        int $userId,
        int $templateId
    ): ?TaskTemplateDTO {
        $model = $this->taskTemplate
            ->query()
            ->where('id', $templateId)
            ->where('user_id', $userId)
            ->first();

        return $model
            ? $this->planningMapper::taskTemplateFromModel($model)
            : null;
    }

    public function getEventTemplateById(
        int $userId,
        int $templateId
    ): ?EventTemplateDTO {
        $model = $this->eventTemplate
            ->query()
            ->where('id', $templateId)
            ->where('user_id', $userId)
            ->first();

        return $model
            ? $this->planningMapper::eventTemplateFromModel($model)
            : null;
    }

    public function getLastTaskByTemplateId(
        int $userId,
        int $templateId
    ): ?TaskDTO {
        $model = $this->task
            ->query()
            ->where('template_id', $templateId)
            ->whereHas('template', fn ($q) => $q->where('user_id', $userId))
            ->latest('id')
            ->first();

        return $model
            ? $this->planningMapper::taskFromModel($model)
            : null;
    }

    public function getLastEventByTemplateId(
        int $userId,
        int $templateId
    ): ?EventDTO {
        $model = $this->event
            ->query()
            ->where('template_id', $templateId)
            ->whereHas('template', fn ($q) => $q->where('user_id', $userId))
            ->latest('id')
            ->first();

        return $model
            ? $this->planningMapper::eventFromModel($model)
            : null;
    }

    public function getTasksForToday(
        int $userId,
        CarbonInterface $from,
        CarbonInterface $to
    ): array {
        $models = $this->task
            ->query()
            ->with('template')
            ->whereHas('template', fn (Builder $q) => $q->where('user_id', $userId))
            ->isActive()
            ->where(function (Builder $q) use ($from, $to) {

                // есть дедлайн
                $q->where(function (Builder $q) use ($from, $to) {
                    $q->whereNotNull('deadline')
                        ->whereBetween('deadline', [$from, $to]);
                })

                    // периодная задача без дедлайна
                    ->orWhere(function (Builder $q) use ($from, $to) {
                        $q->whereNull('deadline')
                            ->where('period_start', '<', now())
                            ->whereBetween('period_end', [$from, $to]);
                    });
            })
            ->get();

        return $models
            ->map(fn ($model) => $this->planningMapper::taskFromModel($model))
            ->all();
    }

    public function countUsersTasksEvents(int $userId): int
    {
        $tasksCount = $this->task
            ->query()
            ->isActive()
            ->whereHas('template', fn (Builder $q) => $q->where('user_id', $userId))
            ->count();

        $eventsCount = $this->event
            ->query()
            ->isActive()
            ->whereHas('template', fn (Builder $q) => $q->where('user_id', $userId))
            ->count();

        return $tasksCount + $eventsCount;
    }
}
