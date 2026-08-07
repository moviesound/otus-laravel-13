<?php

namespace App\Contracts\Bot\Repositories;

interface TagsRepositoryInterface
{
    public function getTaskTags(int $taskTemplateId): array;
    public function getEventTags(int $eventTemplateId): array;
}
