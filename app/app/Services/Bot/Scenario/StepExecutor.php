<?php

namespace App\Services\Bot\Scenario;

use App\Contracts\Bot\Scenario\StateManagerInterface;
use App\Contracts\Bot\Scenario\Steps\StepInterface;
use App\Enums\Bot\StepResultType;
use App\Services\Bot\BotContext;

final class StepExecutor
{
    public function __construct(
        private StepRegistry $registry,
        private StateManagerInterface $stateManager,
    ) {}

    public function execute(StepInterface $step, BotContext $context): void
    {
        $result = $step->handle($context);

        if ($result->type === StepResultType::REPEAT) {
            $step->show($context, $result->error);
            return;
        }

        if ($result->type === StepResultType::SWITCH) {
            $this->stateManager->goto(
                $context,
                $context->scenarioDTO->scenario,
                $result->switchStep,
                $result->data,
                $result->additionalInfo
            );

            if (!$result->switchStep) {
                throw new \RuntimeException('Switch step is empty');
            }

            $next = $this->registry->get($result->switchStep);

            $next->show($context);
        }
    }
}
