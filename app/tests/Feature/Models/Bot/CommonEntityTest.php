<?php

namespace Tests\Feature\Models\Bot;

use App\Models\Bot\EventTemplate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Bot\CommonEntity;
use App\Models\Bot\TaskTemplate;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class CommonEntityTest extends TestCase
{
    use RefreshDatabase;
    //Relation with Task::class
    public function test_common_entity_has_tasks_relation_and_relation_is_same_for_pivot_table()
    {
        $entity = new CommonEntity();

        $relation = $entity->relationType('task');

        $this->assertInstanceOf(
            BelongsToMany::class,
            $relation
        );
    }

    //Relation with Event::class
    public function test_common_entity_has_event_relation_and_relation_is_same_for_pivot_table()
    {
        $entity = new CommonEntity();

        $relation = $entity->relationType('event');

        $this->assertInstanceOf(
            BelongsToMany::class,
            $relation
        );
    }

    public function test_attach_detach_relation_for_task_in_pivot_table()
    {
        $entity = CommonEntity::factory()->create();

        $task = TaskTemplate::factory()->create();

        $entity->attach('task', $task->id);

        $this->assertDatabaseHas(
            'common_entity_task',
            [
                'entity_id' => $entity->id,
                'child_id' => $task->id,
            ]
        );

        $entity->detach('task', $task->id);

        $this->assertDatabaseMissing(
            'common_entity_task',
            [
                'entity_id' => $entity->id,
                'child_id' => $task->id,
            ]
        );
    }

    public function test_attach_detach_relation_for_event_in_pivot_table()
    {
        $entity = CommonEntity::factory()->create();

        $event = EventTemplate::factory()->create();

        $entity->attach('event', $event->id);

        $this->assertDatabaseHas(
            'common_entity_event',
            [
                'entity_id' => $entity->id,
                'child_id' => $event->id,
            ]
        );

        $entity->detach('event', $event->id);

        $this->assertDatabaseMissing(
            'common_entity_event',
            [
                'entity_id' => $entity->id,
                'child_id' => $event->id,
            ]
        );
    }

}
