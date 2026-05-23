<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        
        DB::statement("ALTER TABLE scheduled_posts MODIFY COLUMN status ENUM('pending','processing','published','failed') NOT NULL DEFAULT 'pending'");
    }

    public function down(): void
    {

        DB::table('scheduled_posts')
            ->where('status', 'processing')
            ->update(['status' => 'pending']);

        DB::statement("ALTER TABLE scheduled_posts MODIFY COLUMN status ENUM('pending','published','failed') NOT NULL DEFAULT 'pending'");
    }
};