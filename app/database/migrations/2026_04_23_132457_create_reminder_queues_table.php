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
        Schema::create('reminder_queues', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('reminder_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->enum('channel', ['telegram', 'vk', 'max', 'sms', 'call'])->nullable();
            $table->enum('status', ['pending', 'processing', 'done', 'failed', 'call'])->default('pending');
            $table->tinyInteger('sent_times')->default(0);
            $table->dateTime('last_sent_at')->nullable();
            $table->dateTime('date_remind')->nullable();
            $table->string('process_name', 60)->nullable();
            $table->string('locked_by', 255)->nullable();
            $table->timestamp('locked_at')->nullable();
            $table->timestamps();

            // Индексы
            $table->index('user_id');
            $table->index('channel');
            $table->index('status');
            $table->index('created_at');
            $table->index('reminder_id');
            $table->index('date_remind');
            $table->index('process_name');
            $table->index('locked_by');
            $table->index('locked_at');
            $table->index('sent_times');
            $table->index('last_sent_at');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reminder_queues');
    }
};
