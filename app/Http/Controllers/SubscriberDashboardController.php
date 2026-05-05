<?php

namespace App\Http\Controllers;

use Illuminate\Support\Str;

class SubscriberDashboardController extends Controller
{
   
    public function index()
    {
        $user   = auth()->user();
        $pages  = $user->facebookPages ?? collect();
        $events = $user->scheduledPosts()
            ->get()
            ->map(fn ($post) => [
                'title' => Str::limit($post->content ?? '', 25),
                'start' => $post->scheduled_at?->toIso8601String(),
                'color' => match ($post->status) {
                    'published' => '#10b981',
                    'failed'    => '#ef4444',
                    default     => '#3b82f6',
                },
                'extendedProps' => [
                    'status'  => $post->status,
                    'page'    => $post->facebookPage?->page_name ?? '—',
                    'content' => $post->content ?? '',
                ],
            ]);

        return view('subscriber.dashboard', compact('pages', 'events'));
    }
}