<?php


namespace App\Services\Bot\Scenario\Planning\Steps\RepeatingDates\Dates;

use App\Contracts\Bot\Scenario\Steps\StepInterface;
use App\DTO\Bot\Scenarios\StepResultDTO;
use App\Enums\Bot\MessageIntent;
use App\Services\Bot\BotContext;
use App\Services\Bot\Errors\WrongDataUseButtonsMessage;
use App\Services\Bot\Helpers\Scenarios\Messages\MessageButtons;
use App\Services\Bot\Helpers\Scenarios\Messages\MessageContext;
use App\Services\Bot\Helpers\Scenarios\Messages\MessageIntentResolver;
use App\Services\Bot\Helpers\Scenarios\ScenarioHelper;
use App\Services\Bot\Messengers\MessengerTextResolver;
use App\Services\Bot\Scenario\Planning\Steps\PlanningDoneStep;
use App\Services\Bot\Scenario\Planning\Steps\RepeatingDates\SelectRepeatingOrDateStep;
use App\Services\Bot\Scenario\StepResultFactory;

final class SelectDateModeStep implements StepInterface
{
    public const STEP_KEY = 'selectDateMode';

    public function __construct(
        private readonly MessageIntentResolver      $intentResolver,
        private readonly MessengerTextResolver      $textResolver,
        private readonly WrongDataUseButtonsMessage $wrongDataUseButtonsMessage,
    )
    {
    }

    public static function stepKey(): string
    {
        return self::STEP_KEY;
    }

    public function handle(BotContext $context): StepResultDTO
    {
        $message = trim($context->messageDTO->value());
        $intent = ($this->intentResolver)($message);

        return match ($intent) {
            MessageIntent::Back =>
            StepResultFactory::switch(
                SelectRepeatingOrDateStep::STEP_KEY
            ),

            MessageIntent::Stop =>
            StepResultFactory::switch(
                PlanningDoneStep::STEP_KEY
            ),

            default =>
            $this->handleMessage($context, $message),
        };
    }

    private function handleMessage(BotContext $context, string $message): StepResultDTO
    {
        if ($message === 'deadline') {
            return StepResultFactory::switch(
                AddDeadlineStep::STEP_KEY,
                [
                    'date_mode' => 'deadline',
                ]
            );
        }

        if ($message === 'period') {
            return StepResultFactory::switch(
                AddPeriodStartStep::STEP_KEY,
                [
                    'date_mode' => 'period',
                ]
            );
        }

        return StepResultFactory::repeat(
            $this->wrongDataUseButtonsMessage->get($context)
        );
    }

    public function show(BotContext $context, ?string $error = null): void
    {
        [$messenger, $lang] = MessageContext::getMessengerAndLang($context);

        $data = ScenarioHelper::dataNormalizer($context->scenarioDTO->data);
        $type = $data['type'];

        $answer = $this->buildAnswer($context, $type, $messenger, $lang, $error);
        $buttons = $this->buildButtons($messenger, $lang, $type);

        $context->messenger?->sendMessage($answer, $buttons);
    }

    private function buildAnswer(
        BotContext $context,
        string $type,
        string     $messenger,
        string     $lang,
        ?string    $error,
    ): string
    {


        $textAlias = $type . '_enter_date_or_period';
        $text = $this->textResolver->get(
            $textAlias,
            $messenger,
            $lang
        );

        $headerKey = ScenarioHelper::headerKey($context);

        $header = $this->textResolver->header(
            $messenger,
            $lang,
            $headerKey,
            'task_step_repeating'
        );

        return $context->messenger->format($header, $text, $error);
    }

    private function buildButtons(string $messenger, string $lang, string $type): array
    {
        $btnDeadline = 'deadline_' . ($type === 'event' ? 'event_' : '') . 'emo';
        $btnPeriod = 'period_' . ($type === 'event' ? 'event_' : '') . 'emo';
        return [
            [
                [
                    'text' => $this->textResolver->get($btnDeadline, $messenger, $lang),
                    'callback_data' => 'deadline',
                ],
                [
                    'text' => $this->textResolver->get($btnPeriod, $messenger, $lang),
                    'callback_data' => 'period',
                ],
            ],
            MessageButtons::defaultActions(
                textResolver: $this->textResolver,
                messenger: $messenger,
                lang: $lang,
                backBtn: true,
                cancelBtn: true,
            ),
        ];
    }
}
