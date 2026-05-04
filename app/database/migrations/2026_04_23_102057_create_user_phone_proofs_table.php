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
        Schema::create('user_phone_proofs', function (Blueprint $table) {
            $table->id();//primary

            $table->unsignedBigInteger('user_id')->nullable()->index();//index
            $table->string('phone', 40)->index();//index

            $table->string('code', 10)->index();//index

            $table->enum('status', [
                'sent','success','failed','wrong-code','success-code'
            ])->default('sent')->index();//index

            $table->unsignedInteger('times')->default(1)->index();//index

            $table->string('call_id', 255)->nullable();
            $table->unsignedBigInteger('campaign_id')->nullable();
            $table->decimal('cost', 20, 6)->nullable();

            $table->string('sender', 40)->nullable();
            $table->string('call_status', 60)->nullable()->index();//index

            $table->timestamps();//index
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_phone_proofs');
    }
};
