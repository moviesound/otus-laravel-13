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
        Schema::create('user_socials', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');//primary index
            $table->string('type', 20);//primary index

            $table->string('social_id', 60)->nullable();
            $table->unsignedTinyInteger('is_main')->default(1)->index();//index
            $table->unsignedTinyInteger('keyboard')->default(0)->index();//index
            $table->unsignedBigInteger('current_folder_s3')->nullable();

            $table->timestamps();//created_at index

            // ключи
            $table->unique(['user_id', 'type'], 'user');
            $table->unique(['type', 'id'], 'id');

            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_socials');
    }
};
