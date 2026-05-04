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
        Schema::create('reminder_templates', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('user_id')->nullable();
            $table->text('text')->nullable();
            $table->enum('remind_type', ['hours','days','weeks','months'])->nullable();
            $table->integer('remind_value')->default(1);
            $table->tinyInteger('is_sub_task')->default(0);
            $table->enum('entity_type', ['task','event'])->default('task');
            $table->unsignedBigInteger('entity_id')->nullable();
            $table->tinyInteger('has_call')->default(0);
            $table->tinyInteger('has_sms')->default(0);
            $table->timestamps();

            // Индексы
            $table->index('created_at');
            $table->index('user_id');
            $table->index('remind_type');

            $table->index('entity_type');
            $table->index('entity_id');

            // FULLTEXT
            $table->fullText('text');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reminder_templates');
    }
};
