<?php

namespace App\Repositories\Bot\RemindersRepository;

use App\Contracts\Bot\Repositories\RemindersRepository\SearchRemindersRepositoryInterface;
use App\DTO\Bot\Mappers\ReminderMapper;
use App\DTO\Bot\Models\ReminderDTO;
use App\DTO\Bot\Models\ReminderTemplateDTO;
use App\Models\Bot\Reminder;
use App\Models\Bot\ReminderQueue;
use App\Models\Bot\ReminderTemplate;
use Illuminate\Support\Facades\DB;

class SearchRemindersRepository implements SearchRemindersRepositoryInterface
{
    public function getQueue(
        int $skip,
        int $amount,
    ): array {

        return DB::transaction(function () use ($skip, $amount) {

            $queues = ReminderQueue::query()
                ->isPending()
                ->where('date_remind', '<=', now())
                ->where(function ($query) {
                    $query
                        ->whereNull('locked_at')
                        ->orWhere(
                            'locked_at',
                            '<',
                            now()->subMinutes(5)
                        );
                })
                ->orderBy('date_remind')
                ->offset($skip)
                ->limit($amount)
                ->lockForUpdate()
                ->get();

            foreach ($queues as $queue) {
                $queue->markProcessing();
            }

            return $queues
                ->map(fn (ReminderQueue $queue) =>
                ReminderMapper::reminderQueueFromModel($queue)
                )
                ->all();
        });
    }

    public function getReminderById(int $id): ReminderDTO
    {
        $reminder = Reminder::query()
            ->findOrFail($id);

        return ReminderMapper::reminderFromModel($reminder);
    }


    public function getReminderTemplateById(int $templateId): ReminderTemplateDTO
    {
        $template = ReminderTemplate::query()
            ->findOrFail($templateId);

        return ReminderMapper::reminderTemplateFromModel($template);
    }
}
