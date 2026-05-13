<?php

namespace Tests\Feature\Models\Bot;

use App\Models\Bot\Event;
use App\Models\Bot\EventTemplate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;

class EventTest extends TestCase
{
    use RefreshDatabase;

    public function test_event_belongs_to_template(): void
    {
        $template = EventTemplate::factory()->create();

        $event = Event::factory()->create([
            'template_id' => $template->id,
        ]);

        $this->assertEquals(
            $template->id,
            $event->template->id
        );
    }

    public function test_scope_by_status(): void
    {
        Event::factory()->create(['status' => 'done']);
        Event::factory()->create(['status' => 'pending']);

        $result = Event::byStatus('done')->get();

        $this->assertCount(1, $result);
        $this->assertEquals('done', $result->first()->status);
    }

    public function test_scope_is_active(): void
    {
        Event::factory()->create(['status' => 'pending']);
        Event::factory()->create(['status' => 'processing']);
        Event::factory()->create(['status' => 'done']);

        $result = Event::isActive()->get();

        $this->assertCount(2, $result);
    }

    public function test_scope_is_done(): void
    {
        Event::factory()->create(['status' => 'done']);
        Event::factory()->create(['status' => 'pending']);

        $this->assertCount(1, Event::isDone()->get());
    }

    public function test_scope_deadline_after(): void
    {
        $tomorrow = Carbon::tomorrow();

        Event::factory()->create([
            'deadline' => $tomorrow->copy()->subDay(),
        ]);

        $event = Event::factory()->create([
            'deadline' => $tomorrow->copy()->addDay(),
        ]);

        $result = Event::whereDeadlineAfter($tomorrow)->get();

        $this->assertCount(1, $result);
        $this->assertEquals($event->id, $result->first()->id);
    }

    public function test_scope_deadline_before(): void
    {
        $tomorrow = Carbon::tomorrow();

        $event1 = Event::factory()->create([
            'deadline' => $tomorrow->copy()->subDay(),
        ]);

        Event::factory()->create([
            'deadline' => $tomorrow->copy()->addDay(),
        ]);

        $result = Event::whereDeadlineBefore($tomorrow)->get();

        $this->assertCount(1, $result);
        $this->assertEquals($event1->id, $result->first()->id);
    }

    public function test_scope_deadline_between(): void
    {
        $tomorrow = Carbon::tomorrow();

        Event::factory()->create([
            'deadline' => $tomorrow->copy()->subDays(2),
        ]);

        $event = Event::factory()->create([
            'deadline' => $tomorrow,
        ]);

        Event::factory()->create([
            'deadline' => $tomorrow->copy()->addDays(2),
        ]);

        $result = Event::whereDeadlineBetween(
            $tomorrow->copy()->subDay(),
            $tomorrow->copy()->addDay()
        )->get();

        $this->assertCount(1, $result);
        $this->assertEquals($event->id, $result->first()->id);
    }

    public function test_scope_next_remind_before(): void
    {
        $tomorrow = Carbon::tomorrow();

        $event = Event::factory()->create([
            'next_system_remind_at' => $tomorrow->copy()->subDay(),
        ]);

        Event::factory()->create([
            'next_system_remind_at' => $tomorrow->copy()->addDay(),
        ]);

        $result = Event::whereNextRemindBefore($tomorrow)->get();

        $this->assertCount(1, $result);
        $this->assertEquals($event->id, $result->first()->id);
    }

    public function test_scope_next_remind_after(): void
    {
        $tomorrow = Carbon::tomorrow();

        Event::factory()->create([
            'next_system_remind_at' => $tomorrow->copy()->subDay(),
        ]);

        $event = Event::factory()->create([
            'next_system_remind_at' => $tomorrow->copy()->addDay(),
        ]);

        $result = Event::whereNextRemindAfter($tomorrow)->get();

        $this->assertCount(1, $result);
        $this->assertEquals($event->id, $result->first()->id);
    }

    public function test_scope_period_overlap(): void
    {
        $tomorrow = Carbon::tomorrow();

        // не пересекается
        Event::factory()->create([
            'period_start' => $tomorrow->copy()->subDays(10),
            'period_end'   => $tomorrow->copy()->subDays(5),
        ]);

        // ВОТ этот должен попасть в выборку
        $event = Event::factory()->create([
            'period_start' => $tomorrow->copy()->subDay(),
            'period_end'   => $tomorrow->copy()->addDay(),
        ]);

        // тоже не пересекается
        Event::factory()->create([
            'period_start' => $tomorrow->copy()->addDays(5),
            'period_end'   => $tomorrow->copy()->addDays(10),
        ]);

        $result = Event::wherePeriodOverlap(
            $tomorrow->copy(),
            $tomorrow->copy()
        )->get();

        $this->assertCount(1, $result);
        $this->assertEquals($event->id, $result->first()->id);
    }
}
