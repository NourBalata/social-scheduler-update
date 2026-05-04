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

    Schema::create('content_plans', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained()->cascadeOnDelete();
        $table->string('page_name');
        $table->string('business_type');      // نوع النشاط
        $table->string('audience');           // الجمهور المستهدف
        $table->string('tone');               // الأسلوب (رسمي، ودي، تسويقي)
        $table->string('language')->default('ar');
        $table->date('start_date');
        $table->date('end_date');
        $table->integer('posts_per_week')->default(5);
        $table->enum('status', ['generating', 'ready', 'scheduled'])->default('generating');
        $table->timestamps();
    });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('content_plans');
    }
};
