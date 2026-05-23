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
    Schema::table('users', function (Blueprint $table) {
        if (!Schema::hasColumn('users', 'fb_user_id')) {
            $table->string('fb_user_id')->nullable()->after('password');
        }
        
        if (!Schema::hasColumn('users', 'fb_access_token')) {
            $table->text('fb_access_token')->nullable()->after('fb_user_id');
        }
    });
}
    

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            //
        });
    }
};
