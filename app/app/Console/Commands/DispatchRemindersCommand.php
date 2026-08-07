<?php

namespace App\Console\Commands;

use App\Services\Bot\RemindingProcess\ReminderDispatcher;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('reminders:dispatch')]
#[Description('Dispatch due reminders for processing')]
class DispatchRemindersCommand extends Command
{
    public function __construct(
        private readonly ReminderDispatcher $dispatcher,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $this->info('Searching reminders...');

        $count = $this->dispatcher->dispatch();

        $this->info("Dispatched {$count} reminders.");

        return self::SUCCESS;
    }
}
