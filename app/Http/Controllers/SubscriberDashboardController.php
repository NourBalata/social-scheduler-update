<?php

namespace App\Http\Controllers;

use Illuminate\Support\Str;

class SubscriberDashboardController extends Controller
{
    public function index()
    {
        $user  = auth()->user();
        $pages = $user->facebookPages ?? collect();

        $expiredPages = $pages->filter(
            fn($p) => $p->is_active && $p->token_expires_at?->isPast()
        );

        $expiringPages = $pages->filter(
            fn($p) => $p->is_active &&
                      $p->token_expires_at &&
                      $p->token_expires_at->isFuture() &&
                      $p->token_expires_at->diffInDays(now()) <= 7
        );

        $events = $user->scheduledPosts()
            ->latest()
            ->limit(200)
            ->get()
            ->map(fn ($post) => [
                'id'    => $post->id,
                'title' => Str::limit($post->content ?? '', 25),
                'start' => $post->scheduled_at?->toIso8601String(),
                'extendedProps' => [
                    'status'    => $post->status,
                    'post_type' => $post->post_type ?? 'manual',
                    'page'      => $post->facebookPage?->page_name ?? '—',
                    'content'   => $post->content ?? '',
                ],
            ]);

        return view('subscriber.dashboard', compact('pages', 'events', 'user', 'expiredPages', 'expiringPages'));
    }
}