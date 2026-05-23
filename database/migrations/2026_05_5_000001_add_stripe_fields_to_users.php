<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('stripe_customer_id')->nullable()->after('plan_expires_at');
            $table->string('stripe_subscription_id')->nullable()->after('stripe_customer_id');
            $table->string('stripe_price_id')->nullable()->after('stripe_subscription_id');
            // pending | active | past_due | canceled | trialing
            $table->string('stripe_status')->nullable()->after('stripe_price_id');

            $table->index('stripe_customer_id');
            $table->index('stripe_subscription_id');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['stripe_customer_id']);
            $table->dropIndex(['stripe_subscription_id']);
            $table->dropColumn([
                'stripe_customer_id',
                'stripe_subscription_id',
                'stripe_price_id',
                'stripe_status',
            ]);
        });
    }
};