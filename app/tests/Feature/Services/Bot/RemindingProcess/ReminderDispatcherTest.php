<?php

namespace Tests\Feature\Services\Bot\RemindingProcess;

use App\Contracts\Bot\Repositories\RemindersRepository\SearchRemindersRepositoryInterface;
use App\DTO\Bot\Models\ReminderQueueDTO;
use App\Events\Bot\Reminders\ReminderEvent;
use App\Services\Bot\RemindingProcess\ReminderDispatcher;
use Illuminate\Support\Facades\Event;
use Mockery;
use Tests\TestCase;

class ReminderDispatcherTest extends TestCase
{
    public function test_it_dispatches_reminders(): void
    {
        Event::fake();

        $queue1 = new ReminderQueueDTO(
            id: 1,
            reminder_id: 10,
            user_id: 100,
            channel: 'telegram',
        );

        $queue2 = new ReminderQueueDTO(
            id: 2,
            reminder_id: 20,
            user_id: 200,
            channel: 'telegram',
        );

        $repository = Mockery::mock(
            SearchRemindersRepositoryInterface::class
        );

        $repository
            ->shouldReceive('getQueue')
            ->once()
            ->with(0, 100)
            ->andReturn([$queue1, $queue2]);

        $dispatcher = new ReminderDispatcher($repository);

        $count = $dispatcher->dispatch();

        $this->assertEquals(2, $count);

        Event::assertDispatched(
            ReminderEvent::class,
            function (ReminderEvent $event) use ($queue1) {
                return $event->dto === $queue1;
            }
        );

        Event::assertDispatched(
            ReminderEvent::class,
            function (ReminderEvent $event) use ($queue2) {
                return $event->dto === $queue2;
            }
        );
    }

    public function test_it_returns_zero_when_there_are_no_reminders(): void
    {
        Event::fake();

        $repository = Mockery::mock(
            SearchRemindersRepositoryInterface::class
        );

        $repository
            ->shouldReceive('getQueue')
            ->once()
            ->with(0, 100)
            ->andReturn([]);

        $dispatcher = new ReminderDispatcher($repository);

        $count = $dispatcher->dispatch();

        $this->assertEquals(0, $count);

        Event::assertNotDispatched(ReminderEvent::class);
    }
}
