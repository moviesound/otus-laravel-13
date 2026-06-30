<?php

namespace App\Services\Bot\Scenario\Politics\Steps;

use App\Contracts\Bot\Repositories\UserRepositoryInterface;
use App\Contracts\Bot\Scenario\Steps\StepInterface;
use App\Contracts\SysTextInterface;
use App\Services\Bot\BotContext;
use App\Services\Bot\Helpers\Scenarios\Messages\MessageContext;

final class ConfirmPoliticsStep implements StepInterface
{
    public function __construct(
        private UserRepositoryInterface $userRepo,
        private SysTextInterface $sysText,
    ) {}

    public function handle(BotContext $context): void
    {
        $message = $context->messageDTO->value();

        if (in_array($message, config('messages.approve', []))) {
            $this->userRepo->agreeOnPolitics($context->userDTO->id);

            $context->messenger->sendMessage(
                $this->sysText->get('agreed_on_privacy_politics', $context->userDTO->language)
            );

            // переход дальше:
            MessageContext::setScenario($context, 'onPlanning', 'selectType');

            return;
        }

        $context->messenger->sendMessage(
            $this->sysText->get('confirm_agreement_with_politics', $context->userDTO->language)
        );
    }

    public function show(
        BotContext $context,
        ?string $error = null
    ): void
    {

    }
}
