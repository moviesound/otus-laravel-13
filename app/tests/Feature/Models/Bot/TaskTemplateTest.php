<?php

namespace Tests\Feature\Models\Bot;

use App\Models\Bot\TaskTemplate;
use App\Models\Bot\Task;
use App\Models\Bot\Tag;
use App\Models\Bot\ReminderTemplate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskTemplateTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_belongs_to_user(): void
    {
        $template = TaskTemplate::factory()->create();

        $this->assertNotNull($template->user_id);
    }

    public function test_it_has_tasks_relation(): void
    {
        $template = TaskTemplate::factory()->create();

        Task::factory()->create([
            'template_id' => $template->id,
        ]);

        Task::factory()->create([
            'template_id' => $template->id,
        ]);

        $this->assertCount(2, $template->tasks);
    }

    public function test_it_has_reminders_morph_relation(): void
    {
        $template = TaskTemplate::factory()->create();

        ReminderTemplate::factory()->create([
            'entity_type' => $template->getMorphClass(),
            'entity_id' => $template->id,
        ]);

        $this->assertCount(1, $template->reminders);
    }

    public function test_it_has_tags_relation(): void
    {
        $template = TaskTemplate::factory()->create();
        $tag = Tag::factory()->create();

        $template->tags()->attach($tag->id);

        $this->assertTrue(
            $template->tags->contains($tag)
        );
    }

    public function test_filter_by_user_id(): void
    {
        $t1 = TaskTemplate::factory()->create(['user_id' => 1]);
        $t2 = TaskTemplate::factory()->create(['user_id' => 2]);

        $result = TaskTemplate::query()
            ->byUserId(1)
            ->get();

        $this->assertCount(1, $result);
        $this->assertEquals(1, $result->first()->user_id);
    }

    public function test_filter_active(): void
    {
        TaskTemplate::factory()->create(['status' => 1]);
        TaskTemplate::factory()->create(['status' => 0]);

        $result = TaskTemplate::query()->isActive()->get();

        $this->assertCount(1, $result);
    }

    public function test_filter_repeated(): void
    {
        TaskTemplate::factory()->create(['repeat_type' => 'daily']);
        TaskTemplate::factory()->create(['repeat_type' => 'none']);

        $result = TaskTemplate::query()->isRepeated()->get();

        $this->assertCount(1, $result);
    }

    public function test_ilter_by_status(): void
    {
        TaskTemplate::factory()->create(['status' => 1]);
        TaskTemplate::factory()->create(['status' => 2]);

        $result = TaskTemplate::query()
            ->byStatus(1)
            ->get();

        $this->assertCount(1, $result);
    }

    public function test_filter_by_task_type(): void
    {
        TaskTemplate::factory()->create(['task_type' => 'task']);
        TaskTemplate::factory()->create(['task_type' => 'deadline']);

        $result = TaskTemplate::query()
            ->byTaskType('deadline')
            ->get();

        $this->assertCount(1, $result);
    }

    public function test_filter_with_call(): void
    {
        TaskTemplate::factory()->create(['has_call' => 1]);
        TaskTemplate::factory()->create(['has_call' => 0]);

        $result = TaskTemplate::query()->withCall()->get();

        $this->assertCount(1, $result);
    }

    public function test_filter_with_sms(): void
    {
        TaskTemplate::factory()->create(['has_sms' => 1]);
        TaskTemplate::factory()->create(['has_sms' => 0]);

        $result = TaskTemplate::query()->withSms()->get();

        $this->assertCount(1, $result);
    }

    public function test_it_searches_by_fulltext(): void
    {
        TaskTemplate::factory()->create([
            'title' => 'Call client',
            'description' => 'Important clientsssss meeting',
        ]);

        \DB::statement('ANALYZE TABLE task_templates');//to make fulltext index work

        $result = TaskTemplate::query()
            ->search('client')
            ->get();

        $this->assertNotEmpty($result);
    }

    public function test_get_repeated_helper(): void
    {
        $tpl = TaskTemplate::factory()->create([
            'repeat_type' => 'daily',
        ]);

        $this->assertTrue($tpl->getRepeated());

        $tpl2 = TaskTemplate::factory()->create([
            'repeat_type' => 'none',
        ]);

        $this->assertFalse($tpl2->getRepeated());
    }
}
