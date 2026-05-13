<?php

namespace Tests\Feature\Models\Bot;

use App\Models\Bot\Tag;
use App\Models\Bot\TagTask;
use App\Models\Bot\TaskTemplate;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TagTaskTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_can_create_tag_task(): void
    {
        $pivot = TagTask::factory()->create();

        $this->assertDatabaseHas('tag_task', [
            'id' => $pivot->id,
        ]);
    }

    public function test_filter_by_task_template_id(): void
    {
        $task1 = TaskTemplate::factory()->create();
        $task2 = TaskTemplate::factory()->create();

        TagTask::factory()->forTask($task1->id)->create();
        TagTask::factory()->forTask($task2->id)->create();

        $result = TagTask::query()
            ->byTaskTplId($task1->id)
            ->get();

        $this->assertCount(1, $result);
        $this->assertEquals($task1->id, $result->first()->task_template_id);
    }

    public function test_filter_by_tag_id(): void
    {
        $tag1 = Tag::factory()->create();
        $tag2 = Tag::factory()->create();

        TagTask::factory()->forTag($tag1->id)->create();
        TagTask::factory()->forTag($tag2->id)->create();

        $result = TagTask::query()
            ->byTagId($tag1->id)
            ->get();

        $this->assertCount(1, $result);
        $this->assertEquals($tag1->id, $result->first()->tag_id);
    }

    public function test_it_belongs_to_task_template(): void
    {
        $task = TaskTemplate::factory()->create();

        $pivot = TagTask::factory()->create([
            'task_template_id' => $task->id,
        ]);

        $this->assertEquals(
            $task->id,
            $pivot->taskTemplate->id
        );
    }

    public function test_it_belongs_to_tag(): void
    {
        $tag = Tag::factory()->create();

        $pivot = TagTask::factory()->create([
            'tag_id' => $tag->id,
        ]);

        $this->assertEquals(
            $tag->id,
            $pivot->tag->id
        );
    }

    public function test_validate_unique_tag(): void
    {
        $task = TaskTemplate::factory()->create();
        $tag = Tag::factory()->create();

        TagTask::factory()->forTask($task->id)->forTag($tag->id)->create();

        $this->expectException(QueryException::class);

        TagTask::factory()->forTask($task->id)->forTag($tag->id)->create();
    }
}
