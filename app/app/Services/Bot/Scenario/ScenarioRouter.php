<?php

namespace App\Services\Bot\Scenario;

use App\Contracts\Bot\Scenario\ScenarioRouterInterface;
use App\Contracts\Bot\Scenario\StepExecutorInterface;
use App\Contracts\Bot\Scenario\StepRegistryInterface;
use App\Contracts\SysTextInterface;
use App\Services\Bot\BotContext;

class ScenarioRouter implements ScenarioRouterInterface
{
    public function __construct(
        private readonly StepRegistryInterface $registry,
        private readonly StepExecutorInterface $executor,
        private readonly SysTextInterface      $sysText,
    )
    {
    }

    public function route(BotContext $context): void
    {
        try {
            $step = $this->registry->get(
                $context->scenarioDTO->step
            );

            $this->executor->execute($step, $context);
        } catch (\Throwable $e) {
            logger()->error($e->getMessage() . ' ' . $e->getTraceAsString() . ' ' . $e->getFile() . ' ' . $e->getLine());
            $this->unknown(
                $context
            );
        }
    }

    private function unknown(BotContext $context): void
    {
        $lang = $context->userDTO->language;

        $context->messenger?->sendMessage(
            $this->sysText->get('unknown_command_no_step_bot', $lang)
        );
    }
}
