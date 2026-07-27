<?php

namespace App\Services\Bot\Scenario;

use App\Contracts\Bot\Scenario\StateManagerInterface;
use App\Contracts\Bot\Scenario\StepExecutorInterface;
use App\Contracts\Bot\Scenario\StepRegistryInterface;
use App\Contracts\Bot\Scenario\Steps\StepInterface;
use App\Enums\Bot\StepResultType;
use App\Models\Bot\Step;
use App\Services\Bot\BotContext;

final class StepExecutor implements StepExecutorInterface
{
    public function __construct(
        private StepRegistryInterface $registry,
        private StateManagerInterface $stateManager,
    ) {}

    public function execute(StepInterface $step, BotContext $context): void
    {
        $result = $step->handle($context);

        if ($result->type === StepResultType::FINISH) {
            $context->messenger->deleteUserMessages();
            $context->messenger->deleteSystemMessages();
            Step::destroy($context->userSocialId);
            return;
        }

        if ($result->type === StepResultType::REPEAT) {
            $context->messenger->deleteUserMessages();
            $context->messenger->deleteSystemMessages();
            $step->show($context, $result->error);
            return;
        }

        if ($result->type === StepResultType::SWITCH) {
            if (!$result->switchStep) {
                throw new \RuntimeException('Switch step is empty');
            }

            $this->stateManager->goto(
                $context,
                $context->scenarioDTO->scenario,
                $result->switchStep,
                $result->data,
                $result->additionalInfo
            );

            $context->messenger->deleteUserMessages();
            $context->messenger->deleteSystemMessages();

            $next = $this->registry->get($result->switchStep);

            $next->show($context);
        }
    }
}
