<?php

namespace App\Services\Bot\Scenario\Middleware\Cases;

use App\Contracts\Bot\Repositories\StepRepositoryInterface;
use App\Contracts\Bot\Scenario\Middleware\Cases\PoliticsMiddlewareInterface;
use App\DTO\Bot\State\StepStateDTO;
use App\Services\Bot\BotContext;
use App\Services\Bot\Helpers\Scenarios\Messages\MessageContext;
use App\Services\Bot\Helpers\Scenarios\ScenarioHelper;
use App\Services\Bot\Scenario\Politics\Steps\ConfirmAgreementPoliticsStep;

class PoliticsMiddleware implements PoliticsMiddlewareInterface
{
    public function __construct(
        private StepRepositoryInterface $steps,
    ) {}

    public function handle(BotContext $context): void
    {
        if (
            $context->userDTO->politicsAgreed
            || $context->scenarioDTO->scenario === 'onPolitics'
        ) {
            return;
        }

        //first ENTER in the scenario. Then info is taken from Database

        $stepKey = ConfirmAgreementPoliticsStep::STEP_KEY;

        MessageContext::setScenario(
            $context,
            'onPolitics',
            $stepKey,
            true,
            true
        );

        $this->steps->save(
            new StepStateDTO(
                userSocialId: $context->scenarioDTO->userSocialId,
                scenario: 'onPolitics',
                step: $stepKey,
                message: $context->messageDTO->value(),
                data: $context->scenarioDTO->data,
                additionalInfo: $context->scenarioDTO->additionalInfo,
                commonEntityId: $context->scenarioDTO->commonEntityId,
            )
        );
    }
}
