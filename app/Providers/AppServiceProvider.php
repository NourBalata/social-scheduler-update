<?php

namespace App\Providers;

use App\Contracts\SocialMediaProvider;
use App\Models\User;
use App\Observers\UserObserver;
use App\Services\Social\FacebookProvider;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
    
        $this->app->bind(SocialMediaProvider::class, FacebookProvider::class);
        
    }

    public function boot(): void
    {
       if (app()->environment('production')) {
        \URL::forceScheme('https');
    }
        // User::observe(UserObserver::class);
    }
}