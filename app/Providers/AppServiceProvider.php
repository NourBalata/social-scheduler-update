<?php

namespace App\Providers;
use App\Contracts\SocialMediaProvider;
use App\Services\Social\FacebookProvider;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
       $this->app->bind(SocialMediaProvider::class, FacebookProvider::class);
       \App\Models\User::observe(\App\Observers\UserObserver::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
