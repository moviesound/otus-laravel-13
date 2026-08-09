<?php

namespace App\Events\Bot\Reminders;

use App\DTO\Bot\Models\ReminderQueueDTO;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ReminderEvent
{
    use Dispatchable, SerializesModels;
    public function __construct(public readonly ReminderQueueDTO $dto){}
}
