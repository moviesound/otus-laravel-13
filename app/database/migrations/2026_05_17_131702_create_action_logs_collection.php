<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::connection('mongodb_admin')
            ->getDatabase()
            ->createCollection('action_logs');

        DB::connection('mongodb_admin')
            ->getDatabase()
            ->selectCollection('action_logs')
            ->createIndex([
                'user_id' => 1
            ]);

        DB::connection('mongodb_admin')
            ->getDatabase()
            ->selectCollection('action_logs')
            ->createIndex([
                'user_id' => -1
            ]);

        DB::connection('mongodb_admin')
            ->getDatabase()
            ->selectCollection('action_logs')
            ->createIndex([
                'created_at' => -1
            ]);

        DB::connection('mongodb_admin')
            ->getDatabase()
            ->selectCollection('action_logs')
            ->createIndex([
                'action' => 'text'
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::connection('mongodb_admin')
            ->getDatabase()
            ->dropCollection('action_logs');
    }
};
