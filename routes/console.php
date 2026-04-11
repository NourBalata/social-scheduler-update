<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Console\Commands\PublishScheduledPosts;

use App\Models\ScheduledPost;
use App\Jobs\PublishPostJob;


Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();



// المُنبه بشتغل كل دقيقة
Schedule::call(function () {
    
    // 1. نجيب كل البوستات اللي موعدها إجا ولسه ما انشرت
    $posts = ScheduledPost::ready()->get();

    foreach ($posts as $post) {
        // 2. نبعث البوست للـ Redis عشان الـ Worker يلقطه وينشره
        dispatch(new PublishPostJob($post));
        
        // 3. نغير الحالة مؤقتاً عشان ما يبعته مرة تانية في الدقيقة الجاية
        $post->update(['status' => 'processing']);
    }

})->everyMinute();

Schedule::command("posts:publish")->everyMinute();