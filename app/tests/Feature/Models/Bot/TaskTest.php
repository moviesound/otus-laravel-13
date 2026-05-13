<?php

namespace Tests\Feature\Models\Bot;

use App\Models\Bot\Task;
use App\Models\Bot\TaskTemplate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_belongs_to_template(): void
    {
        $template = TaskTemplate::factory()->create();

        $task = Task::factory()->create([
            'template_id' => $template->id,
        ]);

        $this->assertEquals(
            $template->id,
            $task->template->id
        );
    }

    public function test_filter_by_template_id(): void
    {
        $tpl1 = TaskTemplate::factory()->create();
        $tpl2 = TaskTemplate::factory()->create();

        Task::factory()->create(['template_id' => $tpl1->id]);
        Task::factory()->create(['template_id' => $tpl2->id]);

        $result = Task::query()
            ->byTaskTplId($tpl1->id)
            ->get();

        $this->assertCount(1, $result);
        $this->assertEquals($tpl1->id, $result->first()->template_id);
    }

    public function test_filter_by_status(): void
    {
        Task::factory()->create(['status' => 'pending']);
        Task::factory()->create(['status' => 'done']);

        $result = Task::query()
            ->byStatus('pending')
            ->get();

        $this->assertCount(1, $result);
        $this->assertEquals('pending', $result->first()->status);
    }

    public function test_filter_active_tasks(): void
    {
        Task::factory()->create(['status' => 'pending']);
        Task::factory()->create(['status' => 'processing']);
        Task::factory()->create(['status' => 'done']);

        $result = Task::query()->isActive()->get();

        $this->assertCount(2, $result);
    }

    public function test_filter_done_tasks(): void
    {
        Task::factory()->create(['status' => 'done']);
        Task::factory()->create(['status' => 'pending']);

        $result = Task::query()->isDone()->get();

        $this->assertCount(1, $result);
        $this->assertEquals('done', $result->first()->status);
    }

    public function test_filter_overdue_tasks(): void
    {
        Task::factory()->create(['status' => 'overdue']);
        Task::factory()->create(['status' => 'pending']);

        $result = Task::query()->isOverdue()->get();

        $this->assertCount(1, $result);
    }

    public function test_filter_canceled_tasks(): void
    {
        Task::factory()->create(['status' => 'canceled']);
        Task::factory()->create(['status' => 'pending']);

        $result = Task::query()->isCanceled()->get();

        $this->assertCount(1, $result);
    }

    public function test_filter_deadline_range(): void
    {
        Task::factory()->create([
            'deadline' => now()->subDays(2),
        ]);

        Task::factory()->create([
            'deadline' => now(),
        ]);

        Task::factory()->create([
            'deadline' => now()->addDays(2),
        ]);

        $result = Task::query()
            ->whereDeadlineBetween(
                now()->subDay(),
                now()->addDay()
            )
            ->get();

        $this->assertCount(1, $result);
    }

    public function test_filter_period_overlap(): void
    {
        Task::factory()->create([
            'period_start' => now()->subDays(5),
            'period_end' => now()->addDays(5),
        ]);

        Task::factory()->create([
            'period_start' => now()->addDays(10),
            'period_end' => now()->addDays(20),
        ]);

        $result = Task::query()
            ->wherePeriodOverlap(
                now()->subDay(),
                now()->addDay()
            )
            ->get();

        $this->assertCount(1, $result);
    }

    public function test_filter_next_remind(): void
    {
        Task::factory()->create([
            'next_system_remind_at' => now()->subHour(),
        ]);

        Task::factory()->create([
            'next_system_remind_at' => now()->addHour(),
        ]);

        $result = Task::query()
            ->whereNextRemindBefore(now())
            ->get();

        $this->assertCount(1, $result);
    }

    public function test_helper_methods(): void
    {
        $task = Task::factory()->create(['status' => 'done']);

        $this->assertTrue($task->getDone());
        $this->assertFalse($task->getActive());
    }
}
