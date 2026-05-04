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
        Schema::create('telegram_sys_messages', function (Blueprint $table) {
            $table->id();

            $table->string('chat_id', 60)->nullable();
            $table->string('message_id', 60)->nullable();

            $table->text('message');
            $table->text('query');

            $table->tinyInteger('is_temporary')->default(1);

            $table->timestamp('created_at')->useCurrent();

            $table->unsignedBigInteger('queue_id')->nullable();

            // Индексы
            $table->index('chat_id');
            $table->index('message_id');
            $table->index('is_temporary');
            $table->index('created_at');
            $table->index('queue_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('telegram_sys_messages');
    }
};
