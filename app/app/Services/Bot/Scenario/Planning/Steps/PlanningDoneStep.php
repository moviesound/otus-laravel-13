<?php

namespace App\Services\Bot\Scenario\Planning\Steps;

use App\Contracts\Bot\Repositories\StepRepositoryInterface;
use App\Contracts\Bot\Scenario\Steps\StepInterface;
use App\DTO\Bot\Scenarios\StepResultDTO;
use App\Models\Bot\Step;
use App\Services\Bot\Contexts\BotContext;
use App\Services\Bot\Entities\PlanningEntityCreator;
use App\Services\Bot\Errors\WrongDataUseButtonsMessage;
use App\Services\Bot\Helpers\Messages\MessengerTextResolver;
use App\Services\Bot\Helpers\Scenarios\Messages\MessageContext;
use App\Services\Bot\Helpers\Scenarios\ScenarioHelper;
use App\Services\Bot\Helpers\Scenarios\Summeries\PlanningPreviewFormatter;
use App\Services\Bot\Scenario\StepResultFactory;
use Illuminate\Support\Facades\DB;
use Throwable;

final class PlanningDoneStep implements StepInterface
{
    public const STEP_KEY = 'donePlanningAdding';

    public function __construct(
        private readonly PlanningEntityCreator $creator,
        private readonly PlanningPreviewFormatter $createdFormatter,
        private readonly MessengerTextResolver $messengerTextResolver,
        private readonly WrongDataUseButtonsMessage $wrongDataUseButtonsMessage,
        private readonly StepRepositoryInterface $stepRepo,
    ) {
    }

    public static function stepKey(): string
    {
        return self::STEP_KEY;
    }

    public function handle(BotContext $context): StepResultDTO
    {
        return StepResultFactory::repeat(
            $this->wrongDataUseButtonsMessage->get($context)
        );
    }

    public function show(
        BotContext $context,
        ?string $error = null
    ): void {
        [$messenger, $lang] = MessageContext::getMessengerAndLang($context);

        $data = ScenarioHelper::dataNormalizer(
            $context->scenarioDTO->data
        );

        if (!$this->isValidData($data)) {
            $context->messenger?->sendMessage(
                $this->notCreatedMessage($messenger, $lang)
            );
            $this->stepRepo->clear($context->userSocialId);
            return;
        }

        $data['action'] = 'add';

        DB::beginTransaction();

        try {
            $result = $this->creator->create(
                user: $context->userDTO,
                data: $data,
                channel: $messenger,
            );

            $this->saveCommonEntityToScenarioIfPossible(
                context: $context,
                commonEntityId: $result->id,
            );

            $message = $this->createdFormatter->format(
                data: $data,
                messenger: $messenger,
                lang: $lang,
                done: true
            );

            $context->messenger?->sendMessage(
                text: $message,
                isTemporary: false,
                noSaving: true
            );

            $this->stepRepo->clear($context->userSocialId);

            DB::commit();
        } catch (Throwable $e) {
            DB::rollBack();

            Step::find($context->userSocialId)?->update([
                'step' => AlmostDonePlanningAddingStep::STEP_KEY,
            ]);

            logger()->error($e->getMessage() . PHP_EOL . $e->getFile() . PHP_EOL . $e->getLine() . PHP_EOL . $e->getTraceAsString());
            $context->messenger?->sendMessage(
                text: $this->messengerTextResolver->get('something_went_wrong', $messenger, $lang),
                isTemporary: false,
                noSaving: true
            );
        }
    }

    private function isValidData(array $data): bool
    {
        return !empty($data['type'])
            && !empty($data['subType'])
            && !empty($data['title'])
            && in_array($data['type'], ['task', 'event'], true);
    }

    private function notCreatedMessage(string $messenger, string $lang): string
    {
        return $this->messengerTextResolver->get('no_task_created', $messenger, $lang)
            . ' '
            . $this->messengerTextResolver->get('action_canceled', $messenger, $lang);
    }

    private function saveCommonEntityToScenarioIfPossible(
        BotContext $context,
        int $commonEntityId,
    ): void {
        /**
         * Заглушка для будущей функции, если мы создаем еще какую-то сущность,
         * которая должна быть привязана к задаче/событию:
         * файл, список и т.п., чтобы common_entity_id был единым
         */
    }
}
