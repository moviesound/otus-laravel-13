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
        Schema::create('user_tariffs', function (Blueprint $table) {
            $table->id();//primary

            $table->unsignedBigInteger('user_id');//index
            $table->unsignedInteger('tariff_id');//index

            $table->timestamp('date_start')->useCurrent();//index
            $table->timestamp('date_stop')->nullable();//index

            $table->decimal('cost', 20, 4)->default(0);
            $table->string('currency', 10)->default('RUB');

            // лимиты / usage
            $table->integer('call_tokens_left')->nullable();
            $table->integer('ai_tokens_left')->nullable();

            $table->unsignedBigInteger('ai_tokens_used')->default(0);
            $table->integer('events_during_day')->default(0);
            $table->integer('events_during_month')->default(0);
            $table->integer('notes_during_month')->default(0);
            $table->integer('calls_tokens_used')->nullable();

            $table->tinyInteger('autoprolong')->default(0);
            $table->tinyInteger('prolongation')->default(0);//index

            $table->index(
                ['user_id', 'date_stop'],
                'user_date_stop'
            );
            $table->index(
                ['user_id', 'date_start', 'date_stop'],
                'user_dates'
            );
            $table->index('date_stop');
            $table->index('tariff_id');
            $table->index('prolongation');
            $table->index( ['date_start','date_stop'], 'dates');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_tariffs');
    }
};
