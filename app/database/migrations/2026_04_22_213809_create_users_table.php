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
        Schema::create('users', function (Blueprint $table) {

            $table->id();//primary

            $table->unsignedTinyInteger('is_setted')->default(0)->index();//index
            $table->unsignedTinyInteger('status')->default(1)->index();//index

            $table->string('name', 255);

            $table->unsignedTinyInteger('sex')->nullable()->comment('1 - мужской, 2 - женский');

            $table->string('email', 255)->nullable()->index();//index
            $table->string('phone', 255)->nullable()->index();//index

            $table->unsignedTinyInteger('phone_proved')->default(0)->index();//index

            $table->string('speaker', 40)->default('marina');

            $table->integer('tariff_id')->default(1);

            $table->string('language', 10)->default('ru');

            $table->unsignedBigInteger('location_id')->nullable()->index();//index

            $table->unsignedTinyInteger('birth_day')->nullable();//index
            $table->unsignedTinyInteger('birth_month')->nullable();//index
            $table->integer('birth_year')->nullable();

            $table->unsignedTinyInteger('politics_agreed')->default(0)->index();//index

            $table->string('morning_time_workdays', 10)->default('08:00');
            $table->string('morning_time_holidays', 10)->default('10:00');
            $table->string('evening_time_workdays', 10)->default('21:00');
            $table->string('evening_time_holidays', 10)->default('22:00');

            $table->unsignedTinyInteger('morning_digest_status')->default(1)->index();//index
            $table->unsignedTinyInteger('evening_digest_status')->default(1)->index();//index

            $table->unsignedTinyInteger('digest_currencies')->default(0);
            $table->unsignedTinyInteger('digest_weather')->default(1);

            $table->timestamps();//index

            $table->index(['birth_day', 'birth_month'], 'birth');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
