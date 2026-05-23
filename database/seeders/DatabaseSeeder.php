<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Plan;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            PlanSeeder::class,
        ]);

     $plan = Plan::where('slug', 'free')->first() ?? Plan::first();
     if (!$plan) {
            $plan = Plan::create([
                'name' => 'Free Plan',
                'slug' => 'free',
                'posts_limit' => 10,
                'pages_limit' => 3,
            ]);
        }
        \App\Models\User::updateOrCreate(
            ['email' => 'nour@admin.com'], 
            [
                'name'     => 'Admin Nour',
                'password' => Hash::make('123456789'), 
                'is_admin' => true, 
                'plan_id'  => $plan->id,
            ]
        );
    }
}