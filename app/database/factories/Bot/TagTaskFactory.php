<?php

namespace Database\Factories\Bot;

use App\Models\Bot\TagTask;
use App\Models\Bot\TaskTemplate;
use App\Models\Bot\Tag;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TagTask>
 */
class TagTaskFactory extends Factory
{
    protected $model = TagTask::class;

    public function definition(): array
    {
        return [
            'task_template_id' => TaskTemplate::factory(),
            'tag_id' => Tag::factory(),

            'created_at' => now(),
        ];
    }

    public function forTask(int $taskId): static
    {
        return $this->state(fn () => [
            'task_template_id' => $taskId,
        ]);
    }

    public function forTag(int $tagId): static
    {
        return $this->state(fn () => [
            'tag_id' => $tagId,
        ]);
    }
}
