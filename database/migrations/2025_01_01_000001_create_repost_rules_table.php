<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('repost_rules', function (Blueprint $table) {
            $table->id();

            // البوست الأصلي اللي بنعيد نشره
            $table->foreignId('original_post_id')
                  ->constrained('scheduled_posts')
                  ->cascadeOnDelete();

            // المستخدم والصفحة
            $table->foreignId('user_id')
                  ->constrained()
                  ->cascadeOnDelete();

            $table->foreignId('facebook_page_id')
                  ->constrained()
                  ->cascadeOnDelete();

            // التكرار: weekly أو monthly
            $table->enum('interval', ['weekly', 'monthly']);

            // المحتوى الأصلي — الـ AI بيعيد كتابته كل مرة
            $table->text('original_content');

            // آخر مرة اتنشر
            $table->timestamp('last_reposted_at')->nullable();

            // المرة الجاية للنشر
            $table->timestamp('next_repost_at');

            // نشط أو موقوف
            $table->boolean('is_active')->default(true);

            // عدد مرات إعادة النشر
            $table->unsignedInteger('repost_count')->default(0);

            $table->timestamps();

            $table->index(['is_active', 'next_repost_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('repost_rules');
    }
};