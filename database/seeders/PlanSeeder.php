<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    public function run()
    {
        // خطة Free
        Plan::updateOrCreate(
            ['slug' => 'free'], // المفتاح اللي بنشيك عليه (لو موجود ما بكرره)
            [
                'name' => 'Free',
                'price' => 0,
                'posts_limit' => 10,
                'pages_limit' => 1,
                'active' => true
            ]
        );

        // خطة Pro
        Plan::updateOrCreate(
            ['slug' => 'pro'],
            [
                'name' => 'Pro',
                'price' => 9.99,
                'posts_limit' => 100,
                'pages_limit' => 5,
                'active' => true
            ]
        );

        // خطة Enterprise
        Plan::updateOrCreate(
            ['slug' => 'enterprise'],
            [
                'name' => 'Enterprise',
                'price' => 29.99,
                'posts_limit' => 500,
                'pages_limit' => 20,
                'active' => true
            ]
        );
    }
}