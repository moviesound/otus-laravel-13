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
        Schema::create('reminders', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('template_id');

            $table->dateTime('date_remind')->nullable(); // без default 0000-00-00

            $table->enum('status', [
                'pending',
                'done',
                'processing',
                'sent',
                'failed',
                'call'
            ])->nullable();

            $table->timestamps();


            $table->index('template_id');
            $table->index('date_remind');
            $table->index('status');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reminders');
    }
};
