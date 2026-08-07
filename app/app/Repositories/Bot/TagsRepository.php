<?php

namespace App\Repositories\Bot;

use App\Contracts\Bot\Repositories\TagsRepositoryInterface;
use App\Models\Bot\Tag;

class TagsRepository implements TagsRepositoryInterface
{
    public function getTaskTags(int $taskTemplateId): array
    {
        return Tag::query()
            ->join('tag_task', 'tags.id', '=', 'tag_task.tag_id')
            ->where('tag_task.task_template_id', $taskTemplateId)
            ->orderBy('tags.id')
            ->pluck('tags.tag')
            ->all();
    }

    public function getEventTags(int $eventTemplateId): array
    {
        return Tag::query()
            ->join('event_tag', 'tags.id', '=', 'event_tag.tag_id')
            ->where('event_tag.event_template_id', $eventTemplateId)
            ->orderBy('tags.id')
            ->pluck('tags.tag')
            ->all();
    }
}
