<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

      $this->call([
         PlanSeeder::class,
   
        ]);
        \App\Models\User::updateOrCreate(
        ['email' => 'nour@admin.com'], // إيميل الأدمن الثابت
        [
            'name' => 'Admin Nour',
            'password' => Hash::make('123456789'), 
            'is_admin' => true, 
            'plan_id' => 1,
        ]
    );
    }
}
