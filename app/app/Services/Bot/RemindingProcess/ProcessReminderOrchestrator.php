<?php

namespace App\Services\Bot\RemindingProcess;

use App\Contracts\Bot\Messengers\MessengerFactoryInterface;
use App\Contracts\Bot\Repositories\RemindersRepository\UpdatingRemindersRepositoryInterface;
use App\Services\Bot\Contexts\ReminderContext;
use App\Services\Bot\Helpers\Messages\ReminderButtonsBuilder;
use App\Services\Bot\Helpers\Messages\ReminderMessageBuilder;

class ProcessReminderOrchestrator
{
    public function __construct(
        private readonly ReminderMessageBuilder $messageBuilder,
        private readonly ReminderButtonsBuilder $buttonsBuilder,
        private readonly MessengerFactoryInterface $messengerFactory,
        private readonly UpdatingRemindersRepositoryInterface $repository,
    ) {}

    public function handle(ReminderContext $context): void
    {
        $message = $this->messageBuilder->build($context);

        $buttons = $this->buttonsBuilder->build($context);

        $social = $context->user->mainSocial();

        $messenger = $this->messengerFactory->make(
            chatId: $social->socialId,
            userId: $context->user->id,
            messenger: $social->type,
        );

        $success = $messenger->sendMessage(
            $message,
            $buttons
        );

        if ($success) {
            $this->repository->markSent(
                $context->queue->id
            );
        }
    }
}
