<?php

namespace App\DTO\Bot\Scenarios\Planning;

use Carbon\Carbon;

class SearchPlanningResultDTO
{
    public int $templateId;
    public int $entityId;
    public string $type;
    public int $score;

    public string $title;
    public ?string $description;

    public array $tags;
    public array $reminders;

    public ?Carbon $periodStart;
    public ?Carbon $periodEnd;
    public ?Carbon $deadline;

    public ?int $lastEntityId;
}
