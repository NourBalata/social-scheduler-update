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
        Schema::create('facebook_accounts', function (Blueprint $table) {
            $table->id();
            
            // ربط الحساب بمستخدم النظام عندنا
            $table->foreignId('user_id')
                  ->constrained()
                  ->cascadeOnDelete();

            // البيانات اللي بتيجي من فيسبوك
            $table->string('facebook_id')->unique(); // ID المستخدم عند فيسبوك
            $table->string('name'); // اسمه الكامل على فيسبوك
            $table->string('email')->nullable(); // إيميله (لو احتجناه للتواصل)
            $table->string('avatar')->nullable(); // رابط صورته الشخصية

            // أهم جزء: التوكن الأساسي (User Access Token)
            $table->text('access_token'); 
            
            // متى بنتهي التوكن (الـ Long-lived عادة بكون 60 يوم)
            $table->timestamp('token_expires_at')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('facebook_accounts');
    }
};