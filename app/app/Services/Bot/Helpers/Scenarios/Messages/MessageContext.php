<?php

namespace App\Services\Bot\Helpers\Scenarios\Messages;

use App\DTO\Bot\State\StepStateDTO;
use App\DTO\Bot\User\UserSocialDTO;
use App\Services\Bot\BotContext;

class MessageContext
{
    public static function getMessengerAndLang(BotContext $context): array
    {
        return [
            $context->messenger->name(), //firstly must go a messenger
            $context->userDTO->language, //secondly - a language, like in the name of the function
        ];
    }

    public static function setScenario(BotContext $context, string $scenario, string $step): void
    {
        $context->scenarioDTO = new StepStateDTO(
            userSocialId: $context->scenarioDTO->userSocialId,
            scenario: $scenario,
            step: $step,
            message: $context->scenarioDTO->message,
            data: $context->scenarioDTO->data,
            additionalInfo: $context->scenarioDTO->additionalInfo,
            commonEntityId: $context->scenarioDTO->commonEntityId,
        );
    }

    public static function socialById(BotContext $context, int $userSocialId): ?UserSocialDTO
    {
        foreach ($context->userDTO->userSocials as $social) {
            if ($social->id === $userSocialId) {
                return $social;
            }
        }

        return null;
    }

    private static function getUserSocialIdFromContext(BotContext $context): int
    {
        return $context->scenarioDTO->userSocialId;
    }

    public static function messengerNameByUserSocialId(BotContext $context): ?string
    {
        $userSocialId = self::getUserSocialIdFromContext($context);

        return self::socialById($context, $userSocialId)?->type
            ?? $context->userSocials[0]->type
            ?? null;
    }

    public static function socialIdByUserSocialId(BotContext $context): int|string
    {
        $userSocialId = self::getUserSocialIdFromContext($context);

        return self::socialById($context, $userSocialId)?->socialId
            ?? $context->userSocials[0]->socialId
            ?? 0;
    }
}
