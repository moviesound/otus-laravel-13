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
        Schema::create('user_states', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->primary();//primary

            $table->decimal('balance', 20, 4)->default(0)->index();//index
            $table->string('currency', 10)->default('RUB')->index();//index

            $table->dateTime('next_morning_digest')->nullable()->index();//index
            $table->dateTime('next_evening_digest')->nullable()->index();//index

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_states');
    }
};
