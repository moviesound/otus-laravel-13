<?php

namespace App\Contracts\Bot\Repositories;

use App\DTO\Bot\State\StepStateDTO;

interface StepRepositoryInterface
{
    public function get(int|string $chatId): ?StepStateDTO;

    public function save(StepStateDTO $dto): void;

    public function clear(int $userSocialId): void;
}
