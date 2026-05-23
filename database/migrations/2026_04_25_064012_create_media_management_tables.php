<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. أنشئ المجلدات أولاً لأن المكتبة تعتمد عليها
        Schema::create('media_folders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('color')->nullable();
            $table->foreignId('parent_id')->nullable()->constrained('media_folders')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['user_id', 'name', 'parent_id']);
        });

        // 2. الآن أنشئ المكتبة (media_library)
        Schema::create('media_library', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('filename');
            $table->string('original_filename');
            $table->string('path');
            $table->string('disk')->default('public'); 
            $table->string('mime_type');
            $table->string('type'); 
            $table->unsignedBigInteger('size'); 
            $table->integer('width')->nullable();
            $table->integer('height')->nullable();
            $table->integer('duration')->nullable(); 
            $table->json('metadata')->nullable(); 
            $table->string('thumbnail_path')->nullable();
            
            // الربط هنا صار سليم لأن الجدول موجود فوق
            $table->foreignId('folder_id')->nullable()->constrained('media_folders')->nullOnDelete();
            
            $table->boolean('is_template')->default(false);
            $table->unsignedInteger('usage_count')->default(0);
            $table->timestamp('last_used_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'type']);
            $table->index(['user_id', 'folder_id']);
            $table->index('created_at');
            $table->index('is_template');
        });

        // 3. التاغات
        Schema::create('media_tags', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('color')->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'name']);
        });

        // 4. جدول الربط
        Schema::create('media_tag_pivot', function (Blueprint $table) {
            $table->foreignId('media_id')->constrained('media_library')->cascadeOnDelete();
            $table->foreignId('tag_id')->constrained('media_tags')->cascadeOnDelete();
            $table->primary(['media_id', 'tag_id']);
        });

        // 5. القوالب
        Schema::create('media_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('category');
            $table->json('layers');
            $table->string('thumbnail_path');
            $table->integer('width');
            $table->integer('height');
            $table->boolean('is_public')->default(false);
            $table->unsignedInteger('usage_count')->default(0);
            $table->timestamps();

            $table->index(['user_id', 'category']);
            $table->index('is_public');
        });
    }

    public function down(): void
    {
        // الترتيب العكسي هنا مهم جداً للحذف
        Schema::dropIfExists('media_tag_pivot');
        Schema::dropIfExists('media_templates');
        Schema::dropIfExists('media_library');
        Schema::dropIfExists('media_tags');
        Schema::dropIfExists('media_folders');
    }
};