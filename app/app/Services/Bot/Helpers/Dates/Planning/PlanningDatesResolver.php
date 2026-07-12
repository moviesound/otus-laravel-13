<?php

namespace App\Services\Bot\Helpers\Dates\Planning;

use App\DTO\Bot\Scenarios\Planning\DailyDateDataDTO;
use App\DTO\Bot\Scenarios\Planning\DateResultDTO;
use App\DTO\Bot\Scenarios\Planning\MonthlyDateDataDTO;
use App\DTO\Bot\Scenarios\Planning\NoRepeatDateDataDTO;
use App\DTO\Bot\Scenarios\Planning\PlanningCreateInputDTO;
use App\DTO\Bot\Scenarios\Planning\QuarterlyDateDataDTO;
use App\DTO\Bot\Scenarios\Planning\WeeklyDateDataDTO;
use App\DTO\Bot\Scenarios\Planning\YearlyDateDataDTO;
use InvalidArgumentException;

final readonly class PlanningDatesResolver
{
    public function __construct(
        private NoRepeatDateCalculator $noRepeat,
        private DailyDateCalculator $daily,
        private WeeklyDateCalculator $weekly,
        private MonthlyDateCalculator $monthly,
        private QuarterlyDateCalculator $quarterly,
        private YearlyDateCalculator $yearly,
    ) {
    }

    public function resolve(PlanningCreateInputDTO $input): DateResultDTO
    {
        return match (true) {
            $input->dates instanceof NoRepeatDateDataDTO =>
            $this->noRepeat->calculate($input->context, $input->dates),

            $input->dates instanceof DailyDateDataDTO =>
            $this->daily->calculate($input->context, $input->dates),

            $input->dates instanceof WeeklyDateDataDTO =>
            $this->weekly->calculate($input->context, $input->dates),

            $input->dates instanceof MonthlyDateDataDTO =>
            $this->monthly->calculate($input->context, $input->dates),

            $input->dates instanceof QuarterlyDateDataDTO =>
            $this->quarterly->calculate($input->context, $input->dates),

            $input->dates instanceof YearlyDateDataDTO =>
            $this->yearly->calculate($input->context, $input->dates),

            default => throw new InvalidArgumentException('Unsupported planning date DTO'),
        };
    }
}
