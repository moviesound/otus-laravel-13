<?php

namespace App\Services\Bot\Scenario\Planning\Steps\Search;

use App\Contracts\Bot\Repositories\PlanningRepository\ReadingPlanningRepositoryInterface;
use App\Contracts\Bot\Scenario\Steps\StepInterface;
use App\DTO\Bot\Scenarios\StepResultDTO;
use App\Services\Bot\Contexts\BotContext;
use App\Services\Bot\Helpers\Messages\MessengerTextResolver;
use App\Services\Bot\Helpers\Scenarios\Messages\MessageContext;
use App\Services\Bot\Helpers\Scenarios\ScenarioHelper;
use App\Services\Bot\Helpers\Scenarios\Summeries\PlanningPreviewFormatter;
use App\Services\Bot\Scenario\StepResultFactory;

final class ShowSpecificPlanStep implements StepInterface
{
    public const STEP_KEY = 'showPlanning';

    public function __construct(
        private readonly ReadingPlanningRepositoryInterface $repository,
        private readonly PlanningPreviewFormatter $formatter,
        private readonly MessengerTextResolver $textResolver,
    ) {
    }

    public static function stepKey(): string
    {
        return self::STEP_KEY;
    }

    public function handle(BotContext $context): StepResultDTO
    {
        return StepResultFactory::finish();
    }

    public function show(
        BotContext $context,
        ?string $error = null
    ): void {
        [$messenger, $lang] = MessageContext::getMessengerAndLang($context);

        $data = ScenarioHelper::dataNormalizer(
            $context->scenarioDTO->data
        );

        $type = $data['type'] ?? null;
        $templateId = $data['template_id'] ?? null;

        if (!$type || !$templateId) {
            $context->messenger?->sendMessage(
                $this->text(
                    'something_went_wrong',
                    $messenger,
                    $lang,
                )
            );

            return;
        }

        if ($type === 'task') {
            $template = $this->repository->getTaskTemplateById(
                $context->userDTO->id,
                $templateId,
            );

            $instance = $this->repository->getLastTaskByTemplateId(
                $context->userDTO->id,
                $templateId,
            );
        } else {
            $template = $this->repository->getEventTemplateById(
                $context->userDTO->id,
                $templateId,
            );

            $instance = $this->repository->getLastEventByTemplateId(
                $context->userDTO->id,
                $templateId,
            );
        }

        if (!$template || !$instance) {
            $context->messenger?->sendMessage(
                $this->text(
                    'task_not_found',
                    $messenger,
                    $lang,
                )
            );

            return;
        }

        $message = $this->formatter->format(
            data: $data,
            messenger: $messenger,
            lang: $lang,
        );

        $context->messenger?->sendMessage(
            text: $message,
            buttons: $this->buildButtons(
                $messenger,
                $lang,
                $type,
            ),
        );
    }

    private function buildButtons(
        string $messenger,
        string $lang,
        string $type,
    ): array {
        return [
            [
                [
                    'text' => $this->text(
                        'edit',
                        $messenger,
                        $lang,
                    ),
                    'callback_data' => 'edit',
                ],
                [
                    'text' => $this->text(
                        'delete',
                        $messenger,
                        $lang,
                    ),
                    'callback_data' => 'delete',
                ],
            ],
            [
                [
                    'text' => $this->text(
                        $type === 'task'
                            ? 'complete_task'
                            : 'complete_event',
                        $messenger,
                        $lang,
                    ),
                    'callback_data' => 'complete',
                ],
            ],
        ];
    }

    private function text(
        string $key,
        string $messenger,
        string $lang,
    ): string {
        return $this->textResolver->get(
            $key,
            $messenger,
            $lang,
        );
    }
}
