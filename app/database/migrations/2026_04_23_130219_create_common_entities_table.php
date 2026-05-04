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
        Schema::create('common_entities', function (Blueprint $table) {
            $table->id()
                ->comment('ID объединённой сущности');

            $table->timestamp('created_at')
                ->useCurrent()
                ->nullable()
                ->comment('Дата создания');

            $table->index('created_at');

            $table->comment('Объединённая сущность для связи разных объектов');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('common_entities');
    }
};
