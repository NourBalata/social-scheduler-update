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
        Schema::table('facebook_pages', function (Blueprint $table) {
            $table->string('page_id')->nullable()->change();
            $table->text('access_token')->nullable()->change();
            $table->boolean('is_active')->default(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
    Schema::table('facebook_pages', function (Blueprint $table) {
            $table->string('page_id')->nullable(false)->change();
            $table->text('access_token')->nullable(false)->change();
            $table->boolean('is_active')->default(true)->change();
        });
    }
};
