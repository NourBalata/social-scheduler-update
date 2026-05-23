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
$table->foreignId('facebook_account_id')
                  ->after('user_id') // يضعه بعد عمود user_id لترتيب الجدول
                  ->nullable()       // نضعه nullable مؤقتاً إذا كان هناك بيانات قديمة
                  ->constrained('facebook_accounts')
                  ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('facebook_pages', function (Blueprint $table) {
      $table->dropForeign(['facebook_account_id']);
            $table->dropColumn('facebook_account_id');
        });
    }
};
