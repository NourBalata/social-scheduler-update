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
Schema::create('post_analytics', function (Blueprint $table) {
    $table->id();
    $table->foreignId('scheduled_post_id')->constrained()->cascadeOnDelete();
    $table->integer('likes')->default(0);
    $table->integer('comments')->default(0);
    $table->integer('shares')->default(0);
    $table->integer('reach')->default(0);
    $table->integer('impressions')->default(0);
    $table->json('reactions')->nullable();
    $table->timestamp('fetched_at')->nullable();
    $table->timestamps();
});
}

public function down(): void
{
    Schema::table('facebook_pages', function (Blueprint $table) {
        $table->unique(['user_id', 'page_id']);
    });
}
};
