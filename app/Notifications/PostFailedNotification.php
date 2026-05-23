<?php

namespace App\Notifications;

use App\Models\ScheduledPost;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\DatabaseMessage;

class PostFailedNotification extends Notification
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
            'message'      => 'فشل نشر منشورك على صفحة: ' . $this->post->facebookPage?->page_name,
            'post_id'      => $this->post->id,
            'page'         => $this->post->facebookPage?->page_name,
            'scheduled_at' => $this->post->scheduled_at?->toDateTimeString(),
            'error'        => $this->post->error_message,
        ];
    }
}