<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('salla_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('salla_account_id')->constrained()->cascadeOnDelete();
            $table->string('salla_product_id');
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2)->nullable();
            $table->string('currency', 10)->default('SAR');
            $table->string('image_url')->nullable();
            $table->string('product_url')->nullable();
            $table->string('status')->default('active');
            $table->decimal('sale_price', 10, 2)->nullable();
            $table->timestamps();
            $table->unique(['salla_account_id', 'salla_product_id']);
        });
    }
    public function down(): void { Schema::dropIfExists('salla_products'); }
};