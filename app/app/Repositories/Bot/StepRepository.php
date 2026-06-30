<?php

namespace App\Repositories\Bot;

use App\Contracts\Bot\Repositories\StepRepositoryInterface;
use App\DTO\Bot\State\StepStateDTO;
use App\Models\Bot\Step;

class StepRepository implements StepRepositoryInterface
{
    public function get(int|string $chatId): ?StepStateDTO
    {
        $step = Step::query()
            ->whereHas('userSocial', function ($query) use ($chatId) {
                $query->byType('telegram')
                    ->bySocialId($chatId);
            })
            ->first();

        if (!$step) {
            return null;
        }

        return new StepStateDTO(
            userSocialId: $step->user_social_id,
            scenario: $step->scenario,
            step: $step->step,
            message: $step->message,
            data: $step->data,
            additionalInfo: $step->additional_info,
            commonEntityId: $step->common_entity_id,
        );
    }

    public function save(StepStateDTO $dto): void
    {
        Step::query()->updateOrCreate(
            [
                'user_social_id' => $dto->userSocialId,
            ],
            [
                'scenario' => $dto->scenario,
                'step' => $dto->step,
                'message' => $dto->message,
                'data' => $dto->data,
                'additional_info' => $dto->additionalInfo,
                'common_entity_id' => $dto->commonEntityId,
            ]
        );
    }

    public function clear(int $userSocialId): void
    {
        Step::query()
            ->where('user_social_id', $userSocialId)
            ->delete();
    }
}
