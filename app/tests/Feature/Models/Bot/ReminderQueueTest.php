<?php

namespace Tests\Feature\Models\Bot;

use App\Models\Bot\ReminderQueue;
use App\Models\Bot\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReminderQueueTest extends TestCase
{
    use RefreshDatabase;

    public function test_filter_by_status_scope()
    {
        ReminderQueue::factory()->create(['status' => 'pending']);
        ReminderQueue::factory()->create(['status' => 'failed']);

        $result = ReminderQueue::query()
            ->byStatus('pending')
            ->get();

        $this->assertCount(1, $result);
        $this->assertEquals('pending', $result->first()->status);
    }

    public function test_by_status_returns_all_when_null()
    {
        ReminderQueue::factory()->count(3)->create();

        $result = ReminderQueue::query()
            ->byStatus(null)
            ->get();

        $this->assertCount(3, $result);
    }


    public function test_filter_by_pending_scope()
    {
        ReminderQueue::factory()->create(['status' => 'pending']);
        ReminderQueue::factory()->create(['status' => 'done']);

        $result = ReminderQueue::query()->isPending()->get();

        $this->assertCount(1, $result);
        $this->assertEquals('pending', $result->first()->status);
    }


    public function test_filter_by_processing_scope()
    {
        ReminderQueue::factory()->create(['status' => 'processing']);
        ReminderQueue::factory()->create(['status' => 'pending']);

        $result = ReminderQueue::query()->isProcessing()->get();

        $this->assertCount(1, $result);
        $this->assertEquals('processing', $result->first()->status);
    }


    public function test_filter_by_failed_scope()
    {
        ReminderQueue::factory()->create(['status' => 'failed']);
        ReminderQueue::factory()->create(['status' => 'done']);

        $result = ReminderQueue::query()->isFailed()->get();

        $this->assertCount(1, $result);
        $this->assertEquals('failed', $result->first()->status);
    }


    public function test_filter_by_channel()
    {
        ReminderQueue::factory()->create(['channel' => 'telegram']);
        ReminderQueue::factory()->create(['channel' => 'max']);

        $result = ReminderQueue::query()
            ->byChannel('telegram')
            ->get();

        $this->assertCount(1, $result);
        $this->assertEquals('telegram', $result->first()->channel);
    }


    public function test_filter_by_user_id()
    {
        $user = User::factory()->create();

        ReminderQueue::factory()->create(['user_id' => $user->id]);
        ReminderQueue::factory()->create(['user_id' => null]);

        $result = ReminderQueue::query()
            ->byUserId($user->id)
            ->get();

        $this->assertCount(1, $result);
        $this->assertEquals($user->id, $result->first()->user_id);
    }


    public function test_it_can_mark_as_processing()
    {
        $queue = ReminderQueue::factory()->create([
            'status' => 'pending',
            'locked_by' => null,
        ]);

        $queue->markProcessing('worker-1');

        $this->assertEquals('processing', $queue->fresh()->status);
        $this->assertEquals('worker-1', $queue->fresh()->locked_by);
        $this->assertNotNull($queue->fresh()->locked_at);
    }


    public function test_it_can_mark_as_done()
    {
        $queue = ReminderQueue::factory()->create([
            'status' => 'processing',
            'locked_by' => 'worker',
            'locked_at' => now(),
        ]);

        $queue->markDone();

        $fresh = $queue->fresh();

        $this->assertEquals('done', $fresh->status);
        $this->assertNull($fresh->locked_by);
        $this->assertNull($fresh->locked_at);
    }


    public function test_it_can_mark_as_failed()
    {
        $queue = ReminderQueue::factory()->create([
            'status' => 'processing',
            'locked_by' => 'worker',
            'locked_at' => now(),
        ]);

        $queue->markFailed();

        $fresh = $queue->fresh();

        $this->assertEquals('failed', $fresh->status);
        $this->assertNull($fresh->locked_by);
        $this->assertNull($fresh->locked_at);
    }


    public function test_it_increments_sent_times_and_updates_last_sent_at()
    {
        $queue = ReminderQueue::factory()->create([
            'sent_times' => 2,
        ]);

        $queue->setSent();

        $fresh = $queue->fresh();

        $this->assertEquals(3, $fresh->sent_times);
        $this->assertNotNull($fresh->last_sent_at);
    }


    public function test_it_has_user_relation()
    {
        $user = User::factory()->create();

        $queue = ReminderQueue::factory()->create([
            'user_id' => $user->id,
        ]);

        $this->assertInstanceOf(User::class, $queue->user);
        $this->assertEquals($user->id, $queue->user->id);
    }
}
