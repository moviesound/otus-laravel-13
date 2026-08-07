<?php

namespace App\Services\Bot\RemindingProcess;

use App\Contracts\Bot\Repositories\RemindersRepository\SearchRemindersRepositoryInterface;
use App\DTO\Bot\Mappers\ReminderMapper;
use App\Events\Bot\Reminders\ReminderEvent;

class ReminderDispatcher
{
    public function __construct(
        private readonly SearchRemindersRepositoryInterface $repository,
    )
    {}

    public function dispatch(): int
    {
        // 1. Получить записи из reminder_queue, готовые к обработке
        // 2. Заблокировать их для обработки
        $queues = $this->repository->getQueue(0,100);

        // 3. Для каждой записи отправить ReminderEvent
        if (!empty($queues)) {
            foreach ($queues as $queue) {
                ReminderEvent::dispatch($queue);
            }
        }

        // 4. Вернуть количество отправленных событий
        return count($queues);
    }
}
