<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('salla_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('salla_merchant_id')->unique();
            $table->string('store_name');
            $table->string('store_email')->nullable();
            $table->string('store_avatar')->nullable();
            $table->string('store_url')->nullable();
            $table->text('access_token');
            $table->text('refresh_token')->nullable();
            $table->timestamp('token_expires_at')->nullable();
            $table->boolean('auto_post_enabled')->default(false);
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('salla_accounts'); }
};