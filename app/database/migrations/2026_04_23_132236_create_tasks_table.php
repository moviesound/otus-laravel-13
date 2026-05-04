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
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('template_id')
                ->comment('ID шаблона задачи с настройками');

            $table->enum('status', [
                'pending',
                'processing',
                'done',
                'overdue',
                'canceled'
            ])
                ->default('pending')
                ->comment('Статус задачи');

            $table->dateTime('period_start')
                ->nullable()
                ->comment('Начало периода выполнения задачи');
            $table->dateTime('period_end')
                ->nullable()
                ->comment('Конец периода выполнения задачи');

            $table->dateTime('deadline')
                ->nullable()
                ->comment('Точная дата/время дедлайна задачи');

            $table->timestamp('created_at')
                ->useCurrent()
                ->comment('Дата создания задачи');

            $table->dateTime('check_remind_next_time')
                ->nullable()
                ->comment('Следующая проверка напоминания');

            $table->dateTime('next_system_remind_at')
                ->nullable()
                ->comment('Следующая дата напоминания');

            $table->dateTime('last_shown_in_digest_at')
                ->nullable()
                ->comment('Последний раз показывалась в дайджесте');

            $table->index('status');
            $table->index(['period_start', 'period_end'], 'period');
            $table->index('period_end');
            $table->index('deadline');
            $table->index('check_remind_next_time');

            $table->index('created_at');
            $table->index('next_system_remind_at');
            $table->index('last_shown_in_digest_at');
            $table->index('template_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
