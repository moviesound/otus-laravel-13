<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('task_templates', function (Blueprint $table) {
            $table->enum('date_mode', ['period', 'deadline'])
                ->nullable()
                ->change();
        });

        Schema::table('event_templates', function (Blueprint $table) {
            $table->enum('date_mode', ['period', 'deadline'])
                ->nullable()
                ->default(null)
                ->change();
        });
    }

    public function down(): void
    {
        Schema::table('task_templates', function (Blueprint $table) {
            $table->enum('date_mode', ['period', 'deadline'])
                ->nullable()
                ->change();
        });

        Schema::table('event_templates', function (Blueprint $table) {
            $table->enum('date_mode', ['period', 'deadline'])
                ->default('deadline')
                ->nullable(false)
                ->change();
        });
    }
};
