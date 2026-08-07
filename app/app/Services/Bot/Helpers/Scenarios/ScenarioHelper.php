<?php

namespace App\Services\Bot\Helpers\Scenarios;

use App\DTO\Bot\Scenarios\StepResultDTO;
use App\Enums\Bot\RepeatType;
use App\Services\Bot\Contexts\BotContext;
use App\Services\Bot\Scenario\StepResultFactory;

final class ScenarioHelper
{
    public static function repeatType(string $message): ?RepeatType
    {
        return match ($message) {
            'every_n_days' => RepeatType::Daily,
            'weekly' => RepeatType::Weekly,
            'monthly' => RepeatType::Monthly,
            'quarterly' => RepeatType::Quarterly,
            'yearly' => RepeatType::Yearly,
            default => null,
        };
    }

    public static function handleFirstEnter(BotContext $context, string $step): ?StepResultDTO
    {
        $data = ScenarioHelper::dataNormalizer($context->scenarioDTO->data);

        if (empty($data['resume']['first_enter'])) {
            return null;
        }

        unset($data['resume']['first_enter']);

        return StepResultFactory::switch(
            $step,
            $data,
            $context->scenarioDTO->additionalInfo
        );
    }

    public static function repeatTypeCallback(RepeatType $type): string
    {
        return match ($type) {
            RepeatType::Daily => 'selectDateMode',
            RepeatType::Weekly => 'selectWeekDays',
            RepeatType::Monthly => 'selectMonthDays',
            RepeatType::Quarterly => 'selectQuarterlyType',
            RepeatType::Yearly => 'selectYearlyType',
        };
    }

    public static function headerKey(BotContext $context): string
    {
        $data = self::dataNormalizer($context->scenarioDTO->data);
        return ($data['type'] ?? 'task') === 'task'
            ? 'tasking'
            : 'eventing';
    }

    public static function dataNormalizer(?string $data): array
    {
        if (empty($data)) {
            return [];
        }

        $decoded = json_decode($data, true);

        if (
            json_last_error() !== JSON_ERROR_NONE
            || !is_array($decoded)
        ) {
            return [];
        }

        return $decoded;
    }
}
