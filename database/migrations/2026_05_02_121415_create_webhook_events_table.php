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
    Schema::create('webhook_events', function (Blueprint $table) {
        $table->id();
        $table->string('page_id');
        $table->string('event_type'); // comment, like, post
        $table->string('item_id')->nullable();
        $table->string('from_id')->nullable();
        $table->string('from_name')->nullable();
        $table->text('message')->nullable();
        $table->string('post_id')->nullable();
        $table->json('raw_data');
        $table->boolean('is_read')->default(false);
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('webhook_events');
    }
};
