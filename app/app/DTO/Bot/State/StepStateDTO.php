<?php

namespace App\DTO\Bot\State;

final readonly class StepStateDTO
{
    public function __construct(
        public int $userSocialId,
        public string $scenario,
        public string $step,
        public ?string $message,
        public ?string $data,
        public ?string $additionalInfo,
        public ?int $commonEntityId,
    ) {
    }
}
