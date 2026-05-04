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
        Schema::create('steps', function (Blueprint $table) {
            $table->unsignedBigInteger('user_social_id')->primary();

            $table->string('scenario', 64);
            $table->string('step', 64);

            $table->text('message')->nullable();
            $table->text('data')->nullable();
            $table->text('additional_info')->nullable();

            $table->timestamp('updated_at')
                ->useCurrent()
                ->useCurrentOnUpdate();

            $table->unsignedBigInteger('common_entity_id')->nullable();

            $table->index('common_entity_id');
            $table->index('updated_at');
            $table->index('scenario');
            $table->index('step');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('steps');
    }
};
