<?php

namespace Database\Factories;

use App\Models\TaskTemplate;
use App\Models\User;
use App\Models\Tag;
use App\Support\CustomHelper;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * пример использования
 * TaskTemplate::factory()
 *  ->count(10)
 *  ->withTags(5)
 *  ->create();
 * @extends Factory<TaskTemplate>
 */
class TaskTemplateFactory extends Factory
{
    protected $model = TaskTemplate::class;

    public function definition(): array
    {
        $repeatType = $this->faker->randomElement([
            'none','daily','weekly','monthly','quarterly','yearly'
        ]);
        $isSimpleDateMode = in_array($repeatType, ['none', 'daily']);

        $isDeadline = rand(0,1);

        $dateMode = $isSimpleDateMode
            ? ['period', 'deadline'][$isDeadline]
            : 'period';

        $isCommonTime = rand(0,1);

        $weekDays = $repeatType === 'weekly'
            ? CustomHelper::randomWeekDays()
            : null;

        $monthDays = $repeatType === 'monthly'
            ? CustomHelper::randomMonthDays()
            : null;

        return [
            'user_id' => User::factory(),

            'title' => $this->faker->sentence(3),
            'description' => $this->faker->optional()->text(),

            'repeat_type' => $repeatType,
            'repeat_interval' => $repeatType === 'daily'
                ? rand(1, 3)
                : null,

// DATE MODE (ТОЛЬКО none / daily)
            'date_mode' => $dateMode,

            'period_start' => $dateMode === 'period' ? now()->addDays(rand(1, 7)) : null,
            'period_end'   => $dateMode === 'period' ? now()->addDays(rand(10, 17)) : null,
            'deadline'     => $dateMode === 'deadline' ? now()->addDays(rand(1, 10)) : null,

// WEEKLY
            'week_days' => isset($weekDays) ? implode(',', $weekDays) : null,

            'weekly_common_time' => $isCommonTime === 1 ?
                ($repeatType === 'weekly'
                    ? CustomHelper::makeCommonTime()
                    : null)
                : null,

            'weekly_different_time' => $isCommonTime === 0
                ? ($repeatType === 'weekly'
                    ? CustomHelper::makeDifferentTime($weekDays)
                    : null)
                : null,

// MONTHLY
            'month_days' => isset($monthDays) ? implode(',', $monthDays) : null,

            'monthly_common_time' => $isCommonTime === 1
                ? ($repeatType === 'monthly'
                    ? CustomHelper::makeCommonTime()
                    : null)
                : null,

            'monthly_different_time' => $isCommonTime === 0
                ? ($repeatType === 'monthly'
                    ? CustomHelper::makeDifferentTime($monthDays)
                    : null)
                : null,

// QUARTERLY
            'quarter_type' => $repeatType === 'quarterly'
                ? ['period', 'deadline'][$isDeadline]
                : null,

            'month_in_quarter' => $isDeadline === 1
                ? ($repeatType === 'quarterly'
                    ? rand(1, 3)
                    : null)
                : null,

            'day_in_quarter' => $isDeadline === 1
                ? ($repeatType === 'quarterly'
                    ? rand(1, 28)
                    : null)
                : null,

            'start_month_in_quarter' => $isDeadline === 0
                ? ($repeatType === 'quarterly'
                    ? rand(1, 3)
                    : null)
                : null,

            'start_day_in_quarter' => $isDeadline === 0
                ? ($repeatType === 'quarterly'
                    ? rand(1, 28)
                    : null)
                : null,

            'end_month_in_quarter' => $isDeadline === 0
                ? ($repeatType === 'quarterly'
                    ? rand(1, 3)
                    : null)
                : null,

            'end_day_in_quarter' => $isDeadline === 0
                ? ($repeatType === 'quarterly'
                    ? rand(1, 28)
                    : null)
                : null,

// YEARLY
            'year_type' => $repeatType === 'yearly'
                ? ['period', 'deadline'][$isDeadline]
                : null,

            'month_in_year' => $isDeadline === 1
                ? ($repeatType === 'yearly'
                    ? rand(1, 3)
                    : null)
                : null,

            'day_in_year' => $isDeadline === 1
                ? ($repeatType === 'yearly'
                    ? rand(1, 28)
                    : null)
                : null,

            'start_month_in_year' => $isDeadline === 0
                ? ($repeatType === 'yearly'
                    ? rand(1, 3)
                    : null)
                : null,

            'start_day_in_year' => $isDeadline === 0
                ? ($repeatType === 'yearly'
                    ? rand(1, 28)
                    : null)
                : null,

            'end_month_in_year' => $isDeadline === 0
                ? ($repeatType === 'yearly'
                    ? rand(1, 3)
                    : null)
                : null,

            'end_day_in_year' => $isDeadline === 0
                ? ($repeatType === 'yearly'
                    ? rand(1, 28)
                    : null)
                : null,

// TIME
            'month_start' => rand(1, 12),
            'day_start' => rand(1, 28),
            'hour_start' => rand(0, 23),
            'minute_start' => rand(0, 59),

            'month_end' => rand(1, 12),
            'day_end' => rand(1, 28),
            'hour_end' => rand(0, 23),
            'minute_end' => rand(0, 59),

            'time_set_by_user' => rand(0,1),

// OPTIONS
            'task_type' => $this->faker->randomElement([
                'task','shopping','cleaning','call','write',
                'payment','study','deadline','report'
            ]),

            'status' => 1,
            'has_call' => rand(0, 1),
            'has_sms' => 0,

            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    public function withTags(int $count = 3): static
    {
        return $this->afterCreating(function ($task) use ($count) {

            $tags = Tag::factory()
                ->count($count)
                ->create();

            $task->tags()->attach($tags->pluck('id'));
        });
    }
}
