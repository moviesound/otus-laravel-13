<?php

namespace Tests\Feature\Models\Bot;

use App\Models\Bot\Tag;
use App\Models\Bot\TaskTemplate;
use App\Models\Bot\EventTemplate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TagTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_can_create_tag(): void
    {
        $tag = Tag::factory()->create([
            'tag' => 'laravel',
            'user_id' => 1,
            'count' => 5,
        ]);

        $this->assertDatabaseHas('tags', [
            'id' => $tag->id,
            'tag' => 'laravel',
        ]);
    }

    public function test_filter_by_user_id(): void
    {
        Tag::factory()->create(['user_id' => 1]);
        Tag::factory()->create(['user_id' => 2]);

        $result = Tag::query()
            ->byUserId(1)
            ->get();

        $this->assertCount(1, $result);
        $this->assertEquals(1, $result->first()->user_id);
    }

    public function test_order_by_popularity(): void
    {
        $low = Tag::factory()->create(['count' => 1]);
        $high = Tag::factory()->create(['count' => 10]);

        $result = Tag::query()
            ->isPopular()
            ->get();

        $this->assertEquals($high->id, $result->first()->id);
    }

    public function test_attache_task_template(): void
    {
        $tag = Tag::factory()->create();
        $task = TaskTemplate::factory()->create();

        $tag->taskTemplates()->attach($task->id);

        $this->assertTrue(
            $tag->taskTemplates->contains($task)
        );
    }

    public function test_attache_event_template(): void
    {
        $tag = Tag::factory()->create();
        $event = EventTemplate::factory()->create();

        $tag->eventTemplates()->attach($event->id);

        $this->assertTrue(
            $tag->eventTemplates->contains($event)
        );
    }

    public function test_increase_count(): void
    {
        $tag = Tag::factory()->create(['count' => 0]);

        $tag->increaseCount();

        $this->assertEquals(1, $tag->fresh()->count);
    }

    public function test_decrease_count(): void
    {
        $tag = Tag::factory()->create(['count' => 5]);

        $tag->decreaseCount();

        $this->assertEquals(4, $tag->fresh()->count);
    }

    public function test_it_does_not_decrease_below_zero(): void
    {
        $tag = Tag::factory()->create(['count' => 0]);

        $tag->decreaseCount();

        $this->assertEquals(0, $tag->fresh()->count);
    }
}
