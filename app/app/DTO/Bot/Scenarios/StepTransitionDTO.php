<?php

namespace App\DTO\Bot\Scenarios;

final readonly class StepTransitionDTO
{
    public function __construct(
        public ?string $scenario = null,
        public ?string $step = null,
        public ?string $message = null,
        public ?string $data = null,
        public ?string $additionalInfo = null,
        public ?int $commonEntityId = null,
    ) {}
}
