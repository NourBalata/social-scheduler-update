<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\FacebookPage;
use Illuminate\Database\Eloquent\Factories\Factory;

class ScheduledPostFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id'          => User::factory(),
            'facebook_page_id' => FacebookPage::factory(),
            'content'          => fake()->sentence(),
            'media_url'        => null,
            'media_type'       => null,
            'scheduled_at'     => now()->addHour(),
            'status'           => 'pending',
            'published_at'     => null,
            'fb_post_id'       => null,
            'error_message'    => null,
        ];
    }
}