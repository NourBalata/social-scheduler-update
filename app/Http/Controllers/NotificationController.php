<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;

class NotificationController extends Controller
{
    public function index(): JsonResponse
    {
        $notifications = auth()->user()
            ->unreadNotifications()
            ->latest()
            ->take(20)
            ->get()
            ->map(fn ($n) => [
                'id'         => $n->id,
                'type'       => $n->data['type'] ?? 'failed',
                'message'    => $n->data['message'],
                'page'       => $n->data['page'] ?? null,
                'error'      => $n->data['error'] ?? null,
                'time'       => $n->created_at->diffForHumans(),
                'created_at' => $n->created_at->toDateTimeString(),
            ]);

        return response()->json([
            'notifications' => $notifications,
            'count'         => $notifications->count(),
        ]);
    }

    public function markAllRead(): JsonResponse
    {
        auth()->user()->unreadNotifications()->update(['read_at' => now()]);

        return response()->json(['success' => true]);
    }
}