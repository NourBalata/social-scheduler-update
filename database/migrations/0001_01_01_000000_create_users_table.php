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
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            
            // حقول الاشتراك اللي ضفتيها
            $table->enum('plan', ['free', 'pro'])->default('free');
            $table->timestamp('plan_expires_at')->nullable();
            $table->integer('posts_this_month')->default(0);
            $table->string('fb_user_id')->nullable();

            // الحقول الجديدة اللي بنحتاجها عشان الـ Dynamic Config
            $table->string('fb_client_id')->nullable();
            $table->string('fb_client_secret')->nullable();

            $table->rememberToken();
            $table->timestamps();
        });

        // جداول لارفيل الأساسية
        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // بما إننا استخدمنا create فوق، الـ down لازم يحذف الجداول بالكامل
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
    }
};