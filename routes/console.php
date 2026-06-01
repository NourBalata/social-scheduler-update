<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;


Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();


Schedule::command('posts:publish')->everyMinute()->withoutOverlapping();

// كل 15 دقيقة: تنقذ البوستات اللي عالقة بـ processing لو الـ worker مات
Schedule::command('posts:rescue')->everyFifteenMinutes()->withoutOverlapping();



Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();


Schedule::command('posts:publish')->everyMinute()->withoutOverlapping();

// كل 15 دقيقة: تنقذ البوستات اللي عالقة بـ processing لو الـ worker مات
Schedule::command('posts:rescue')->everyFifteenMinutes()->withoutOverlapping();

// كل يوم الساعة 8 صباحاً: شيل البوستات الجاهزة لإعادة النشر
Schedule::command('reposts:process')->dailyAt('08:00')->withoutOverlapping();