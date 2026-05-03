<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
   public function up()
{
    Schema::table('scheduled_posts', function (Blueprint $table) {
        $table->index('user_id');
        $table->index('status');
        $table->index('scheduled_at');
    });

    Schema::table('facebook_pages', function (Blueprint $table) {
        $table->index('user_id');
    });
}

public function down()
{
    Schema::table('scheduled_posts', function (Blueprint $table) {
        $table->dropIndex(['user_id']);
        $table->dropIndex(['status']);
        $table->dropIndex(['scheduled_at']);
    });

    Schema::table('facebook_pages', function (Blueprint $table) {
        $table->dropIndex(['user_id']);
    });
}
};
