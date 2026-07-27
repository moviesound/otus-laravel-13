<?php

namespace App\Contracts\Bot\Repositories\PlanningRepository;

interface SearchPlanningRepositoryInterface
{
    public function searchTasksEventsByScore(int $userId, string $text, int $limit = 50, int $offset = 0): array;

    public function searchAllUsersTasksEvents(int $userId, int $limit = 20, int $offset = 0): array;
}
