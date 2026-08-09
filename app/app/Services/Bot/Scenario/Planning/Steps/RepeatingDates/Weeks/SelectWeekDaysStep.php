<?php

namespace App\Services\Bot\Scenario\Planning\Steps\RepeatingDates\Weeks;

use App\Contracts\Bot\Scenario\Steps\StepInterface;
use App\DTO\Bot\Scenarios\StepResultDTO;
use App\Enums\Bot\MessageIntent;
use App\Services\Bot\Contexts\BotContext;
use App\Services\Bot\Errors\WrongDataUseButtonsMessage;
use App\Services\Bot\Helpers\Messages\MessengerTextResolver;
use App\Services\Bot\Helpers\Scenarios\Messages\MessageButtons;
use App\Services\Bot\Helpers\Scenarios\Messages\MessageContext;
use App\Services\Bot\Helpers\Scenarios\Messages\MessageIntentResolver;
use App\Services\Bot\Helpers\Scenarios\ScenarioHelper;
use App\Services\Bot\Scenario\Planning\Steps\PlanningDoneStep;
use App\Services\Bot\Scenario\Planning\Steps\RepeatingDates\SelectRepeatTypeStep;
use App\Services\Bot\Scenario\StepResultFactory;

final class SelectWeekDaysStep implements StepInterface
{
    public const STEP_KEY = 'selectWeekDays';

    public function __construct(
        private readonly MessengerTextResolver      $textResolver,
        private readonly MessageIntentResolver      $intentResolver,
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
                SelectRepeatTypeStep::STEP_KEY,
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
        $data = ScenarioHelper::dataNormalizer($context->scenarioDTO->data);

        // DONE
        if ($message === 'wd_done') {
            if (empty($data['week_days'])) {
                return StepResultFactory::repeat(
                    $this->textResolver->get('choose_at_least_one_day', $this->getMessenger($context), $this->getLang($context))
                );
            }

            return StepResultFactory::switch(AskWeeklyTimeAddStep::STEP_KEY);
        }

        // toggle day
        if (str_starts_with($message, 'wd_')) {
            $day = (int)substr($message, 3);

            $days = $this->toggleWeekDay($data, $day);

            return StepResultFactory::switch(
                self::STEP_KEY,
                [
                    'week_days' => implode(',', $days),
                ]
            );
        }

        return StepResultFactory::repeat(
            $this->wrongDataUseButtonsMessage->get($context)
        );
    }

    private function toggleWeekDay(array $data, int $day): array
    {
        $days = isset($data['week_days'])
            ? explode(',', $data['week_days'])
            : [];

        $day = (string)$day;

        if (in_array($day, $days, true)) {
            $days = array_values(array_diff($days, [$day]));
        } else {
            $days[] = $day;
        }

        $days = array_values(array_filter($days));

        sort($days, SORT_NUMERIC);

        return [
            'week_days' => implode(',', $days),
        ];
    }

    public function show(BotContext $context, ?string $error = null): void
    {
        $messenger = $this->getMessenger($context);
        $lang = $this->getLang($context);

        $buttons = $this->buildButtons($context, $messenger, $lang);

        $context->messenger?->sendMessage(
            $this->textResolver->get('choose_week_days', $messenger, $lang),
            $buttons
        );
    }

    private function buildButtons(BotContext $context, string $messenger, string $lang): array
    {
        $data = ScenarioHelper::dataNormalizer($context->scenarioDTO->data);

        $selectedDays = isset($data['week_days'])
            ? explode(',', $data['week_days'])
            : [];

        $days = [
            '1' => $this->textResolver->get('monday_short', $messenger, $lang),
            '2' => $this->textResolver->get('tuesday_short', $messenger, $lang),
            '3' => $this->textResolver->get('wednesday_short', $messenger, $lang),
            '4' => $this->textResolver->get('thursday_short', $messenger, $lang),
            '5' => $this->textResolver->get('friday_short', $messenger, $lang),
            '6' => $this->textResolver->get('saturday_short', $messenger, $lang),
            '7' => $this->textResolver->get('sunday_short', $messenger, $lang),
        ];

        foreach ($days as $k => $label) {
            $items[] = [
                'name' => in_array($k, $selectedDays, true)
                    ? "✔️ $label"
                    : $label,
                'callback' => "wd_$k",
            ];
        }

        $buttons = MessageButtons::make(
            $items,
            $this->textResolver,
            $messenger,
            $lang,
        );

        $buttons[] =
            MessageButtons::defaultActions(
                textResolver: $this->textResolver,
                messenger: $messenger,
                lang: $lang,
                backBtn: true,
                cancelBtn: true,
            );

        return $buttons;
    }

    private function getMessenger(BotContext $context): string
    {
        return MessageContext::getMessengerAndLang($context)[0];
    }

    private function getLang(BotContext $context): string
    {
        return MessageContext::getMessengerAndLang($context)[1];
    }
}
