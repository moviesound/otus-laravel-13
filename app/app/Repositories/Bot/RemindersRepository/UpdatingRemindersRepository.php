<?php

namespace App\Repositories\Bot\RemindersRepository;

use App\Contracts\Bot\Repositories\RemindersRepository\UpdatingRemindersRepositoryInterface;
use App\Models\Bot\ReminderQueue;

class UpdatingRemindersRepository implements UpdatingRemindersRepositoryInterface
{
    public function markSent(int $queueId): void
    {
        ReminderQueue::query()
            ->whereKey($queueId)
            ->increment(
                column: 'sent_times',
                amount: 1,
                extra: [
                    'last_sent_at' => now(),
                ]
            );
    }
}
