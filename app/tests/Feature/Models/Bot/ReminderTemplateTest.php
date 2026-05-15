<?php

namespace Tests\Feature\Models\Bot;

use App\Models\Bot\EventTemplate;
use App\Models\Bot\Reminder;
use App\Models\Bot\ReminderTemplate;
use App\Models\Bot\TaskTemplate;
use App\Models\Bot\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ReminderTemplateTest extends TestCase
{
    use RefreshDatabase;


    public function test_filter_by_user_id()
    {
        $user = User::factory()->create();

        ReminderTemplate::factory()->create(['user_id' => $user->id]);
        ReminderTemplate::factory()->create();

        $result = ReminderTemplate::query()
            ->byUserId($user->id)
            ->get();

        $this->assertCount(1, $result);
    }


    public function test_filter_by_entity_id_scope()
    {
        ReminderTemplate::factory()->create([
            'entity_type' => 'task',
            'entity_id' => 10,
        ]);

        ReminderTemplate::factory()->create([
            'entity_type' => 'task',
            'entity_id' => 20,
        ]);

        $result = ReminderTemplate::query()
            ->byEntityId('task', 10)
            ->get();

        $this->assertCount(1, $result);
        $this->assertEquals(10, $result->first()->entity_id);
    }


    public function test_filter_by_entity_type_only_when_id_is_null()
    {
        ReminderTemplate::factory()->create([
            'entity_type' => 'task',
            'entity_id' => 1,
        ]);

        ReminderTemplate::factory()->create([
            'entity_type' => 'event',
            'entity_id' => 2,
        ]);

        $result = ReminderTemplate::query()
            ->byEntityId('task')
            ->get();

        $this->assertCount(1, $result);
    }


    public function test_filter_sub_tasks()
    {
        ReminderTemplate::factory()->subTask()->create();
        ReminderTemplate::factory()->mainTask()->create();

        $result = ReminderTemplate::query()->isSubTask()->get();

        $this->assertCount(1, $result);
        $this->assertEquals(1, $result->first()->is_sub_task);
    }

    public function test_it_has_reminders_relation()
    {
        $template = ReminderTemplate::factory()->create();

        $reminders = Reminder::factory()
            ->count(3)
            ->create([
                'template_id' => $template->id,
            ]);

        $this->assertCount(3, $template->reminders);
    }

    public function test_it_belongs_to_task_template_entity()
    {
        $task = TaskTemplate::factory()->create();

        $template = ReminderTemplate::factory()
            ->forTaskTemplate($task)
            ->create();

        $this->assertInstanceOf(
            TaskTemplate::class,
            $template->entity
        );

        $this->assertEquals($task->id, $template->entity->id);
    }

    public function it_belongs_to_event_template_entity()
    {
        $event = EventTemplate::factory()->create();

        $template = ReminderTemplate::factory()
            ->forEventTemplate($event)
            ->create();

        $this->assertInstanceOf(
            EventTemplate::class,
            $template->entity
        );
    }

}
