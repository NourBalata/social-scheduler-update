<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('post_analytics')) {
        Schema::create('post_analytics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('scheduled_post_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('likes')->default(0);
            $table->unsignedInteger('reach')->default(0);
            $table->unsignedInteger('impressions')->default(0);
            $table->timestamp('fetched_at');
            $table->timestamps();

            $table->index('scheduled_post_id');
        });
    }
    }

    public function down(): void
    {
        Schema::dropIfExists('post_analytics');
    }
};