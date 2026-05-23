<?php

namespace App\Notifications;

use App\Models\ScheduledPost;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class PostPublishedNotification extends Notification
{
    use Queueable;

    public function __construct(private ScheduledPost $post) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type'         => 'success',
            'message'      => 'تم نشر منشورك بنجاح على صفحة: ' . $this->post->facebookPage?->page_name,
            'post_id'      => $this->post->id,
            'page'         => $this->post->facebookPage?->page_name,
            'published_at' => $this->post->published_at?->toDateTimeString(),
        ];
    }
}