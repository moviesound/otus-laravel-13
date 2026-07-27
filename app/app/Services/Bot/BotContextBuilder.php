<?php

namespace App\Services\Bot;

use App\Contracts\Bot\Messengers\MessengerFactoryInterface;
use App\Contracts\Bot\Repositories\StepRepositoryInterface;
use App\Contracts\Bot\Repositories\UserRepositoryInterface;
use App\Contracts\Bot\Scenario\ScenarioResolverInterface;
use App\Contracts\Bot\Users\UserResolverInterface;
use App\DTO\Bot\BotInput;
use App\DTO\Bot\Message\MessageDTO;
use App\Enums\Bot\MessageType;

class BotContextBuilder
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepo,
        private readonly UserResolverInterface $userResolver,
        private readonly StepRepositoryInterface $stepRepo,
        private readonly ScenarioResolverInterface $scenarioResolver,
        private readonly MessengerFactoryInterface $messengerFactory,
    ) {}

    public function build(BotInput $input): BotContext
    {
        // 1. user
        $user = $this->userResolver->resolve(
            chatId: $input->chatId,
            messenger: $input->messenger
        );

        // 2. user_socials.id
        $userSocialId = $this->userRepo->getSocialUserIdByChatIdAndMessengerType(
            chatId: $input->chatId,
            messangerType: $input->messenger
        );

        // 2. state
        $state = $this->stepRepo->get($userSocialId,  $input->messenger);

        // 3. resolve scenario
        $resolvedState = $this->scenarioResolver->resolve(
            state: $state,
            userSocialId: $userSocialId,
            message: $input->text
        );

        if (
            $state === null
            && $resolvedState->scenario !== 'unknown'
            && $resolvedState->step !== 'unknown'
        ) {
            $this->stepRepo->save($resolvedState);
        }

        // 4. message DTO
        $messageDTO = new MessageDTO(
            type: $input->text ? MessageType::Message : MessageType::Callback,
            text: $input->text,
            raw: [],
        );

        // 5. messenger
        $messenger = $this->messengerFactory->make(
            chatId: $input->chatId,
            userId: $user->id,
            messenger: $input->messenger
        );

        // 6. final context
        return new BotContext(
            userDTO: $user,
            userSocialId: $userSocialId,
            scenarioDTO: $resolvedState,
            messageDTO: $messageDTO,
            messenger: $messenger,
        );
    }
}
