<?php

namespace Tests\Feature\Models\Bot;

use App\Models\Bot\Reminder;
use App\Models\Bot\ReminderQueue;
use App\Models\Bot\ReminderTemplate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ReminderTest extends TestCase
{
    use RefreshDatabase;

    public function test_scope_by_tpl_id(): void
    {
        Reminder::factory()->create(['template_id' => 1]);
        $target = Reminder::factory()->create(['template_id' => 2]);

        $result = Reminder::byTplId(2)->get();

        $this->assertCount(1, $result);
        $this->assertEquals($target->id, $result->first()->id);
    }

    public function test_scope_by_status(): void
    {
        Reminder::factory()->create(['status' => 'done']);
        $target = Reminder::factory()->create(['status' => 'pending']);

        $result = Reminder::byStatus('pending')->get();

        $this->assertCount(1, $result);
        $this->assertEquals($target->id, $result->first()->id);
    }

    public function test_scope_by_status_null_returns_all(): void
    {
        Reminder::factory()->count(3)->create(['status' => 'pending']);

        $result = Reminder::byStatus(null)->get();

        $this->assertCount(3, $result);
    }

    public function test_scope_where_date_remind_after(): void
    {
        Reminder::factory()->create([
            'date_remind' => now()->subDay(),
        ]);

        $target = Reminder::factory()->create([
            'date_remind' => now()->addDays(2),
        ]);

        $result = Reminder::whereDateRemindAfter(now()->addDay())->get();

        $this->assertCount(1, $result);
        $this->assertEquals($target->id, $result->first()->id);
    }

    public function test_scope_where_date_remind_before(): void
    {
        $target = Reminder::factory()->create([
            'date_remind' => now()->addDay(),
        ]);

        Reminder::factory()->create([
            'date_remind' => now()->addDays(5),
        ]);

        $result = Reminder::whereDateRemindBefore(now()->addDays(2))->get();

        $this->assertCount(1, $result);
        $this->assertEquals($target->id, $result->first()->id);
    }

    public function test_scope_upcoming(): void
    {
        Reminder::factory()->create([
            'date_remind' => now()->subDay(),
            'status' => 'pending',
        ]);

        $target = Reminder::factory()->create([
            'date_remind' => now()->addDay(),
            'status' => 'pending',
        ]);

        $result = Reminder::upcoming()->get();

        $this->assertCount(1, $result);
        $this->assertEquals($target->id, $result->first()->id);
    }

    public function test_scope_overdue(): void
    {
        Reminder::factory()->create([
            'date_remind' => now()->subDays(2),
            'status' => 'done',
        ]);

        $target = Reminder::factory()->create([
            'date_remind' => now()->subDays(2),
            'status' => 'pending',
        ]);

        $result = Reminder::overdue()->get();

        $this->assertCount(1, $result);
        $this->assertEquals($target->id, $result->first()->id);
    }

    public function test_it_belongs_to_template(): void
    {
        $reminder = Reminder::factory()->create();

        $this->assertInstanceOf(
            ReminderTemplate::class,
            $reminder->template
        );
    }

    public function test_it_has_reminder_queues_relation(): void
    {
        $reminder = Reminder::factory()->create();

        ReminderQueue::factory()->create([
            'reminder_id' => $reminder->id,
        ]);

        ReminderQueue::factory()->create([
            'reminder_id' => $reminder->id,
        ]);

        $this->assertCount(2, $reminder->reminderQueues);
    }
}
