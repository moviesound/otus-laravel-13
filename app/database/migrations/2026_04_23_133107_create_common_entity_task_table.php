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
        Schema::create('common_entity_task', function (Blueprint $table) {
            $table->id()
                ->comment('ID записи связи');

            $table->unsignedBigInteger('entity_id')
                ->comment('ID объединённой сущности');

            $table->unsignedBigInteger('child_id')
                ->comment('ID конкретного объекта');


            $table->timestamp('created_at')
                ->nullable()
                ->useCurrent()
                ->comment('Дата добавления объекта в сущность');

            $table->index(['child_id'], 'idx_type_child_id');
            $table->index('created_at');
            $table->unique(['entity_id', 'child_id'], 'idx_entity_id_type_child_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('common_entity_task');
    }
};
