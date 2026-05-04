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
        Schema::create('sys_texts', function (Blueprint $table) {
            $table->id();

            $table->string('alias', 60);
            $table->string('lang', 10)->default('ru');

            $table->text('context');

            $table->timestamps();

            $table->unique(['alias', 'lang'], 'alias');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sys_texts');
    }
};
