<?php

namespace App\Services\Bot\Scenario\Politics\Steps;

use App\Contracts\Bot\Scenario\Steps\StepInterface;
use App\DTO\Bot\Scenarios\StepResultDTO;
use App\Models\Bot\User;
use App\Services\Bot\Contexts\BotContext;
use App\Services\Bot\Errors\WrongDataUseButtonsMessage;
use App\Services\Bot\Helpers\Messages\MessengerTextResolver;
use App\Services\Bot\Helpers\Scenarios\Messages\MessageContext;
use App\Services\Bot\Helpers\Scenarios\ScenarioHelper;
use App\Services\Bot\Scenario\StepResultFactory;

final class ConfirmAgreementPoliticsStep implements StepInterface
{
    public const STEP_KEY = 'ConfirmAgreementPolitics';

    public function __construct(
        private readonly MessengerTextResolver $textResolver,
        private readonly WrongDataUseButtonsMessage $wrongDataUseButtonsMessage,
    ) {}

    public static function stepKey(): string
    {
        return self::STEP_KEY;
    }

    public function handle(BotContext $context): StepResultDTO
    {
        $message = $context->messageDTO->value();

        if ($message === 'yes') {
            return $this->agree($context);
        }

        if ($message === 'no') {
            return $this->decline($context);
        }

        if ($result = ScenarioHelper::handleFirstEnter($context, self::STEP_KEY)) {
            return $result;
        }

        return StepResultFactory::repeat(
            $this->wrongDataUseButtonsMessage->get($context)
        );
    }

    private function agree(BotContext $context): StepResultDTO
    {
        $this->sendResult('agreed_on_privacy_politics', $context);

        $data = ScenarioHelper::dataNormalizer($context->scenarioDTO->data);
        $resume = $data['resume'] ?? null;

        $this->setPoliticsAgreed($context, 1);

        if (!empty($resume)) {
            //switch to new step
            MessageContext::setScenario(
                context: $context,
                scenario: $resume['scenario'],
                step: $resume['step']
            );
            return StepResultFactory::switch($resume['step']);
        } else {
            return StepResultFactory::finish();
        }
    }

    private function setPoliticsAgreed(BotContext $context, int $val): void
    {
        User::byUserId($context->userDTO->id)
            ->update([
                'politics_agreed' => $val,
            ]);
        $context->userDTO->politicsAgreed = $val;
    }

    private function decline(BotContext $context): StepResultDTO
    {
        $this->setPoliticsAgreed($context, 0);

        $this->sendResult('disagreed_on_privacy_politics', $context, false, true);

        return StepResultFactory::finish();
    }

    public function sendResult(string $text, BotContext $context, $isTemporary = true, $noSaving = false): void
    {
        [$messenger, $lang] = MessageContext::getMessengerAndLang($context);

         $answer = $this->textResolver->get(
             $text,
             $messenger,
             $lang
         );

        $answer = $context->messenger->format(
            '',
            $answer,
            ''
        );

        $buttons = [];

        $context->messenger?->sendMessage(
            $answer,
            $buttons,
            $isTemporary,
            $noSaving,
        );
    }

    public function show(BotContext $context, ?string $error = null): void
    {
        [$messenger, $lang] = MessageContext::getMessengerAndLang($context);

        $answer = $this->textResolver->get(
            'confirm_agreement_with_politics',
            $messenger,
            $lang,
            ['url' => 'https://yozh.app/faq/politics']
        );

        $answer = $context->messenger->format(
            '',
            $answer,
            $error
        );

        $buttons = $this->getButtons($messenger, $lang);

        $context->messenger?->sendMessage(
            $answer,
            $buttons
        );
    }

    private function getButtons(string $messenger, string $lang): array
    {
        $buttons = [
            [
                [
                    'text' => $this->textResolver->get(
                        'yes',
                        $messenger,
                        $lang
                    ),
                    'callback_data' => 'yes',
                ],
                [
                    'text' => $this->textResolver->get(
                        'no',
                        $messenger,
                        $lang
                    ),
                    'callback_data' => 'no',
                ],
            ]
        ];

        return  $buttons;
    }
}
