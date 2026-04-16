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
   
        $table->dropForeign(['user_id']);
        $table->dropUnique('facebook_pages_user_id_page_id_unique');
        $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
    });
}

public function down(): void
{
    Schema::table('facebook_pages', function (Blueprint $table) {
        $table->unique(['user_id', 'page_id']);
    });
}
};
