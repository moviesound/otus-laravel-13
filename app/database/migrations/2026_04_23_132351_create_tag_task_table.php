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
        Schema::create('tag_task', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('task_template_id');
            $table->unsignedBigInteger('tag_id');

            $table->timestamp('created_at')->useCurrent();

            $table->index('tag_id');
            $table->index('created_at');

            // защита от дублей связей
            $table->unique(['task_template_id', 'tag_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tag_task');
    }
};
