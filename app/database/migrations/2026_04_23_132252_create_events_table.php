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
        Schema::create('events', function (Blueprint $table) {
            $table->id()
                ->comment('ID события');

            $table->unsignedBigInteger('template_id')
                ->nullable()
                ->comment('ID шаблона события с настройками');

            $table->enum('status', [
                'pending',
                'processing',
                'done',
                'overdue',
                'canceled'
            ])->default('pending')
                ->comment('Статус события');

            $table->dateTime('period_start')
                ->nullable()
                ->comment('Начало периода выполнения');

            $table->dateTime('period_end')
                ->nullable()
                ->comment('Конец периода выполнения');

            $table->dateTime('deadline')
                ->nullable()
                ->comment('Точный дедлайн');

            $table->timestamp('created_at')
                ->useCurrent()
                ->comment('Дата создания');

            $table->dateTime('check_remind_next_time')
                ->nullable()
                ->comment('Следующая проверка напоминания');

            $table->dateTime('next_system_remind_at')
                ->nullable()
                ->comment('Следующее напоминание');


            $table->index('template_id');
            $table->index('status');
            $table->index(['period_start', 'period_end'], 'period');
            $table->index('period_end');
            $table->index('check_remind_next_time');
            $table->index('deadline');
            $table->index('created_at');
            $table->index('next_system_remind_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
