<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('task_templates', function (Blueprint $table) {
            $table->id()->comment('ID шаблона задачи');
            $table->unsignedBigInteger('user_id');
            $table->string('title')->comment('Название задачи');
            $table->text('description')->nullable()->comment('Описание задачи');
            $table->enum('repeat_type', ['none','daily','weekly','monthly','quarterly','yearly'])->default('none')->comment('Тип повторения задачи');
            $table->tinyInteger('repeat_interval')->nullable()->comment('Интервал повторения');
            $table->set('week_days', ['1','2','3','4','5','6','7'])->nullable()->comment('Дни недели для weekly');
            $table->text('weekly_common_time')->nullable()->comment('Общее время для всех выбранных дней недели');
            $table->text('weekly_different_time')->nullable()->comment('Разное время для выбранных дней недели');
            $table->string('month_days', 50)->nullable()->comment('Дни месяца для monthly');
            $table->text('monthly_common_time')->nullable()->comment('Общее время для всех выбранных дней месяца');
            $table->text('monthly_different_time')->nullable()->comment('Разное время для всех выбранных дней месяца');
            $table->enum('quarter_type', ['deadline','period'])->nullable()->comment('Тип ежеквартальной задачи (дедлайн или период');
            $table->tinyInteger('month_in_quarter')->nullable()->comment('Номер месяца внутри квартала для дедлайна');
            $table->string('day_in_quarter', 60)->nullable()->comment('День в квартале для дедлайна');
            $table->string('start_month_in_quarter', 3)->nullable()->comment('Номер месяца квартала для начала периода выполнения задачи');
            $table->string('start_day_in_quarter', 3)->nullable()->comment('Номер дня в месяце квартала для окончания периода выполнения задачи');
            $table->string('end_month_in_quarter', 3)->nullable()->comment('Номер месяца квартала для окончания периода выполнения задачи');
            $table->string('end_day_in_quarter', 3)->nullable()->comment('Номер дня в месяце квартала для окончания периода выполнения задачи');
            $table->enum('year_type', ['deadline','period'])->nullable()->comment('Тип ежегодной задачи: дедлайн или период');
            $table->tinyInteger('month_in_year')->nullable()->comment('Номер месяца в году для дедлайна');
            $table->string('day_in_year', 60)->nullable()->comment('Номер дня в месяце для ежегодного дедлайна');
            $table->string('start_month_in_year', 3)->nullable()->comment('Номер месяца для начала периода выполнения ежегодной задачи');
            $table->string('start_day_in_year', 3)->nullable()->comment('Номер дня в месяце начала периода ежегодной задачи');
            $table->string('end_month_in_year', 3)->nullable()->comment('Номер месяца для конца периода выполнения ежегодной задачи');
            $table->string('end_day_in_year', 3)->nullable()->comment('Номер дня в месяце конца периода ежегодной задачи');
            $table->tinyInteger('month_start')->nullable();
            $table->tinyInteger('day_start')->nullable();
            $table->tinyInteger('hour_start')->nullable();
            $table->tinyInteger('minute_start')->nullable();
            $table->tinyInteger('month_end')->nullable();
            $table->tinyInteger('day_end')->nullable();
            $table->tinyInteger('hour_end')->nullable();
            $table->tinyInteger('minute_end')->nullable();
            $table->tinyInteger('time_set_by_user')->default(0)->comment('Флаг, что время (часы и минуты) дедлайна в задаче установлены пользователем, а не взяты системные настройки');
            $table->enum('date_mode', ['period','deadline'])->nullable();
            $table->dateTime('period_start')->nullable();
            $table->dateTime('period_end')->nullable();
            $table->dateTime('deadline')->nullable();
            $table->timestamp('created_at')->useCurrent()->comment('Дата создания шаблона');
            $table->timestamp('updated_at')->nullable()->useCurrentOnUpdate()->comment('Дата последнего обновления');
            $table->enum('task_type', ['task','shopping','cleaning','call','write','payment','study','deadline','report'])->nullable()->comment('Подтипы задач');
            $table->tinyInteger('status')->default(2)->comment('3 - edit, 2 - creation, 1 - active, 0 - deleted');
            $table->tinyInteger('has_call')->default(0)->comment('Пользователь хочет обязательное напоминание звоноком');
            $table->tinyInteger('has_sms')->default(0)->comment('Пользователь хочет обязательное напоминание через смс');

            // Индексы
            $table->index('user_id');
            $table->index('repeat_type');
            $table->index('created_at');
            $table->index('updated_at');
            $table->index('status');
            $table->index('has_call');
            $table->index('has_sms');
            $table->index('time_set_by_user');
            $table->index('task_type');

            // FULLTEXT
            $table->fullText('title');
            $table->fullText('description');

            $table->comment('Шаблоны задач с их настройками');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('task_templates');
    }
};
