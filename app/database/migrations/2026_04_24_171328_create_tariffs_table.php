<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tariffs', function (Blueprint $table) {

            $table->id();

            $table->string('name', 60)->nullable();

            $table->text('description')->nullable();

            $table->integer('period_days', false, true)->nullable()->index();

            $table->timestamp('date_start')->useCurrent()->index();

            $table->timestamp('date_stop')->nullable()->index();

            $table->integer('status')->default(1)->index()
                ->comment('1 - on, 2 - off (no prolongation)');

            $table->decimal('cost', 20, 4)->default(0);

            $table->string('currency', 3)->default('RUB')->index();

            $table->integer('calls_tokens')->default(0)->index();

            $table->integer('events_per_day')->nullable()->index();

            $table->integer('events_per_month')->nullable()->index();

            $table->integer('notes_per_month')->nullable()->index();

            $table->integer('total_notes')->nullable()->index();

            $table->integer('total_lists')->nullable()->index();

            $table->integer('items_in_list')->nullable()->index();

            $table->integer('ai_tokens')->default(0)->index();

            $table->bigInteger('files_volume')->default(0)->index();

            $table->boolean('shared_space')->default(false)->index();

            $table->boolean('default')->default(false)->index();

            $table->boolean('autoprolong')->default(false)->index();

            $table->boolean('prolongation')->default(false)->index();

            $table->string('action_placeholder', 60)->nullable()->index();

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tariffs');
    }
};
