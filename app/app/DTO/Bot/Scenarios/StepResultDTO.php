<?php

namespace App\DTO\Bot\Scenarios;

use App\Enums\Bot\StepResultType;

final readonly class StepResultDTO
{
    public function __construct(
        public StepResultType $type,
        public ?string $switchStep = null,
        public  array $data = [],
        public ?string $error = null,
        public mixed $additionalInfo = null,
    ) {}
}
