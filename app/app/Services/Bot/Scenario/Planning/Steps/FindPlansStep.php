<?php

namespace App\Services\Bot\Scenario\Planning\Steps;

use App\Contracts\Bot\Scenario\Steps\StepInterface;
use App\DTO\Bot\Scenarios\StepResultDTO;
use App\Services\Bot\BotContext;
use App\Services\Bot\Scenario\StepResultFactory;

class FindPlansStep implements StepInterface
{
    public const STEP_KEY = 'findPlans';

    public function __construct(
    ) {
    }

    public static function stepKey(): string
    {
        return self::STEP_KEY;
    }

    public function handle(BotContext $context): StepResultDTO
    {
        return StepResultFactory::repeat(
            ''
        );
    }

    public function show(
        BotContext $context,
        ?string $error = null
    ): void
    {

    }
}
