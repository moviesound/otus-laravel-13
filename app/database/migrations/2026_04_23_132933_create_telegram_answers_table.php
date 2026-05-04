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
        Schema::create('telegram_answers', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('chat_id', 60)->nullable();

            $table->timestamp('created_at')
                ->useCurrent();

            $table->text('message')->nullable();
            $table->string('message_id', 60)->nullable();
            $table->text('debug')->nullable();

            $table->enum('type', ['user', 'system', 'callback'])->nullable();

            $table->tinyInteger('status')
                ->default(0);
            $table->tinyInteger('is_temporary')
                ->default(1);

            $table->string('locked_by', 60)->nullable();
            $table->timestamp('locked_at')->nullable();

            $table->index('user_id');
            $table->index('chat_id');
            $table->index('created_at');
            $table->index('message_id');
            $table->index('type');
            $table->index('status');
            $table->index('is_temporary');
            $table->index('locked_by');
            $table->index('locked_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('telegram_answers');
    }
};
