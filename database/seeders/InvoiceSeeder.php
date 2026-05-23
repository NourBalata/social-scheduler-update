<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class InvoiceSeeder extends Seeder
{
    public function run(): void
{
    $users = DB::table('users')->pluck('id')->toArray();
    $plans = DB::table('plans')->where('price', '>', 0)->get();

    $invoices = [];

 
    for ($month = 11; $month >= 0; $month--) {
        $date = now()->subMonths($month)->startOfMonth();

        $subscribedUsers = collect($users)->random(rand(20, 50));

        foreach ($subscribedUsers as $userId) {
            $plan = $plans->random();

            $invoices[] = [
                'user_id'                  => $userId,
                'plan_id'                  => $plan->id,
                'stripe_invoice_id'        => 'inv_demo_' . uniqid(),
                'stripe_payment_intent_id' => 'pi_demo_' . uniqid(),
                'status'                   => 'paid',
                'amount'                   => $plan->price,
                'currency'                 => 'usd',
                'period_start'             => $date->copy()->startOfMonth(),
                'period_end'               => $date->copy()->endOfMonth(),
                'paid_at'                  => $date->copy()->addDays(rand(1, 5)),
                'created_at'               => $date->copy()->startOfMonth(),
                'updated_at'               => $date->copy()->startOfMonth(),
            ];
        }
    }

    DB::table('subscription_invoices')->insert($invoices);

    $this->command->info('✅ Inserted ' . count($invoices) . ' invoices.');
}
}