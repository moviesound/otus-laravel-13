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
        Schema::create('event_templates', function (Blueprint $table) {
            $table->id()->comment('ID шаблона события');

            $table->unsignedBigInteger('user_id')
                ->nullable()
                ->comment('ID пользователя');

            $table->string('title')
                ->comment('Название шаблона');

            $table->text('description')
                ->nullable()
                ->comment('Описание шаблона');

            $table->enum('repeat_type', [
                'none','daily','weekly','monthly','quarterly','yearly'
            ])->default('none')->comment('Частота повторения');

            $table->tinyInteger('repeat_interval')->nullable()
                ->comment('Интервал сколько дней между повторениями');

            $table->set('week_days', ['1','2','3','4','5','6','7'])->nullable()->comment('Еженедельное повторение по каким дням недели');

            $table->text('weekly_common_time')->nullable();
            $table->text('weekly_different_time')->nullable();

            $table->string('month_days', 50)->nullable();

            $table->text('monthly_common_time')->nullable();
            $table->text('monthly_different_time')->nullable();

            $table->enum('quarter_type', ['deadline','period'])->nullable();
            $table->tinyInteger('month_in_quarter')->nullable();
            $table->string('day_in_quarter', 60)->nullable();

            $table->string('start_month_in_quarter', 3)->nullable();
            $table->string('start_day_in_quarter', 3)->nullable();
            $table->string('end_month_in_quarter', 3)->nullable();
            $table->string('end_day_in_quarter', 3)->nullable();

            $table->enum('year_type', ['deadline','period'])->nullable();
            $table->tinyInteger('month_in_year')->nullable();
            $table->string('day_in_year', 60)->nullable();

            $table->string('start_month_in_year', 3)->nullable();
            $table->string('start_day_in_year', 3)->nullable();
            $table->string('end_month_in_year', 3)->nullable();
            $table->string('end_day_in_year', 3)->nullable();

            $table->tinyInteger('month_start')->nullable();
            $table->tinyInteger('day_start')->nullable();
            $table->tinyInteger('hour_start')->nullable();
            $table->tinyInteger('minute_start')->nullable();

            $table->tinyInteger('month_end')->nullable();
            $table->tinyInteger('day_end')->nullable();
            $table->tinyInteger('hour_end')->nullable();
            $table->tinyInteger('minute_end')->nullable();

            $table->enum('date_mode', ['period', 'deadline'])
                ->default('deadline')
                ->comment('Определяет тип даты: период или дедлайн');
            $table->dateTime('period_start')->nullable();
            $table->dateTime('period_end')->nullable();
            $table->dateTime('deadline')->nullable();
            $table->tinyInteger('time_set_by_user')->default(0);

            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrentOnUpdate();

            $table->enum('event_type', [
                'meeting','birthday','holiday','trip','anniversary',
                'medication','pet_walk','exercise','meal','appointment','event'
            ])->nullable();

            $table->tinyInteger('status')->default(2)
                ->comment('3 - edit mode, 2 - creation mode, 1 - active, 0 - deleted');



            $table->boolean('has_call')->default(0);
            $table->boolean('has_sms')->default(0);


            $table->index('repeat_type');
            $table->index('created_at');
            $table->index('updated_at');
            $table->index('status');
            $table->index('user_id');
            $table->index('has_call');
            $table->index('has_sms');
            $table->index('time_set_by_user');
            $table->index('event_type');

            $table->fullText('title');
            $table->fullText('description');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event_templates');
    }
};
