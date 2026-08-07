<?php

namespace App\Listeners\Bot\Reminders;

use App\Events\Bot\Reminders\ReminderEvent;
use App\Services\Bot\Contexts\ReminderContextBuilder;
use App\Services\Bot\RemindingProcess\ProcessReminderOrchestrator;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\Attributes\Queue;

#[Queue('reminders')]
class ProcessReminderListener implements ShouldQueue
{
    public function __construct(
        private readonly ReminderContextBuilder $contextBuilder,
        private readonly ProcessReminderOrchestrator $orchestrator,
    ) {}

    public function handle(ReminderEvent $event): void
    {
        $context = $this->contextBuilder->build($event->dto);

        $this->orchestrator->handle($context);
    }
}
