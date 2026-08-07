<?php

namespace App\Contracts\Bot\Repositories\RemindersRepository;

interface UpdatingRemindersRepositoryInterface
{
    public function markSent(int $queueId): void;
}
