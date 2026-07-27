<?php

namespace App\Services\Bot\Scenario;

use App\DTO\Bot\Scenarios\StepResultDTO;
use App\Enums\Bot\StepResultType;

final class StepResultFactory
{
    public function __construct(
        public StepResultDTO $dto,
    )
    {
    }

    public static function switch(
        string $switchStep,
        array  $data = [],
        mixed $additionalInfo = null,
    ): StepResultDTO
    {
        return new StepResultDTO(
            type: StepResultType::SWITCH,
            switchStep: $switchStep,
            data: $data,
            additionalInfo: $additionalInfo,
        );
    }

    public static function repeat(
        ?string $error = null
    ): StepResultDTO
    {
        return new StepResultDTO(
            type: StepResultType::REPEAT,
            error: $error,
        );
    }

    public static function finish(): StepResultDTO
    {
        return new StepResultDTO(
            type: StepResultType::FINISH,
        );
    }
}
