<?php

namespace App\Http\Controllers;

use App\Models\RepostRule;
use App\Models\ScheduledPost;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RepostRuleController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'post_id'  => 'required|exists:scheduled_posts,id',
            'interval' => 'required|in:weekly,monthly',
        ]);

        $post = ScheduledPost::where('id', $request->post_id)
            ->where('user_id', auth()->id()) // ← بس بوستاته هو
            ->where('status', 'published')
            ->firstOrFail();

        $exists = RepostRule::where('original_post_id', $post->id)
            ->where('is_active', true)
            ->exists();

        if ($exists) {
            return response()->json(['success' => false, 'message' => 'Rule already exists.'], 422);
        }

        RepostRule::create([
            'original_post_id' => $post->id,
            'user_id'          => auth()->id(),
            'facebook_page_id' => $post->facebook_page_id,
            'interval'         => $request->interval,
            'original_content' => $post->content,
            'next_repost_at'   => $request->interval === 'weekly' ? now()->addWeek() : now()->addMonth(),
            'is_active'        => true,
        ]);

        return response()->json(['success' => true]);
    }

    public function toggle(Request $request, RepostRule $rule): JsonResponse
    {
        abort_if($rule->user_id !== auth()->id(), 403);
        $rule->update(['is_active' => $request->is_active]);
        return response()->json(['success' => true]);
    }

    public function destroy(RepostRule $rule): JsonResponse
    {
        abort_if($rule->user_id !== auth()->id(), 403);
        $rule->delete();
        return response()->json(['success' => true]);
    }
}