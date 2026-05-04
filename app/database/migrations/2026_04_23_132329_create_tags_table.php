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
        Schema::create('tags', function (Blueprint $table) {
            $table->id();

            $table->string('tag')
                ->default('')
                ->comment('Тег');

            $table->timestamp('created_at')
                ->useCurrent();

            $table->unsignedInteger('user_id')
                ->default(0);

            $table->unsignedInteger('count')
                ->default(0);

            $table->index('created_at');
            $table->index('user_id');
            $table->index('count');
            $table->index('tag');

            $table->fullText('tag');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tags');
    }
};
