<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('scheduled_posts', function (Blueprint $table) {
            // composite index للـ query الأكثر استخداماً في PublishScheduledPosts command
            // WHERE user_id = ? AND status = ? AND scheduled_at <= ?
            $table->index(['user_id', 'status', 'scheduled_at'], 'posts_user_status_scheduled');
        });

        Schema::table('facebook_pages', function (Blueprint $table) {
            $table->index('user_id', 'pages_user_id');
        });
    }

    public function down(): void
    {
        Schema::table('scheduled_posts', function (Blueprint $table) {
            $table->dropIndex('posts_user_status_scheduled');
        });

        Schema::table('facebook_pages', function (Blueprint $table) {
            $table->dropIndex('pages_user_id');
        });
    }
};