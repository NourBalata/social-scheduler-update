<?php

namespace App\Http\Controllers;

use App\Models\ScheduledPost;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class PostRetryController extends Controller
{

    private array $errorPatterns = [
        'token' => [
            'patterns' => ['token', 'oauth', 'session', 'expired', 'invalid_token', 'access token'],
            'type'     => 'token_expired',
            'title'    => '🔑 Token Expired',
            'fix'      => 'reconnect_page',
            'message'  => 'صلاحية الصفحة انتهت. يجب إعادة ربط الصفحة بـ Facebook.',
            'action'   => 'Reconnect Page',
        ],
        'permission' => [
            'patterns' => ['permission', 'scope', 'publish_pages', 'pages_manage_posts', 'unauthorized'],
            'type'     => 'permission_missing',
            'title'    => ' Missing Permission',
            'fix'      => 'reconnect_page',
            'message'  => 'الصفحة تفتقر لصلاحية النشر. أعد ربط الصفحة وتأكد من منح صلاحية "Manage Posts".',
            'action'   => 'Reconnect Page',
        ],
        'content' => [
            'patterns' => ['community standards', 'policy', 'spam', 'abusive', 'violates', 'prohibited', 'blocked content'],
            'type'     => 'content_policy',
            'title'    => ' Content Policy Violation',
            'fix'      => 'rewrite_content',
            'message'  => 'المحتوى يخالف سياسات Facebook. الـ AI سيعيد صياغته ليكون متوافقاً.',
            'action'   => 'Rewrite & Retry',
        ],
        'media' => [
            'patterns' => ['photo', 'video', 'media', 'image', 'file', 'upload', 'format', 'size'],
            'type'     => 'media_error',
            'title'    => 'Media Error',
            'fix'      => 'remove_media',
            'message'  => 'المشكلة بالصورة أو الفيديو. سيتم النشر كمنشور نصي فقط.',
            'action'   => 'Post Without Media',
        ],
        'rate_limit' => [
            'patterns' => ['rate limit', 'too many', 'request limit', 'throttle', 'calls exceeded'],
            'type'     => 'rate_limit',
            'title'    => ' Rate Limit Reached',
            'fix'      => 'delay_post',
            'message'  => 'وصلت للحد الأقصى من الطلبات. سيتم إعادة الجدولة بعد ساعتين تلقائياً.',
            'action'   => 'Reschedule +2 Hours',
        ],
        'page_not_found' => [
            'patterns' => ['does not exist', 'page not found', 'invalid page', 'no such object', 'object does not'],
            'type'     => 'page_not_found',
            'title'    => ' Page Not Found',
            'fix'      => 'reconnect_page',
            'message'  => 'الصفحة غير موجودة أو تم حذفها. يجب إعادة ربط صفحة صحيحة.',
            'action'   => 'Reconnect Page',
        ],
        'duplicate' => [
            'patterns' => ['duplicate', 'already posted', 'same content', 'identical'],
            'type'     => 'duplicate_content',
            'title'    => 'Duplicate Content',
            'fix'      => 'rewrite_content',
            'message'  => 'Facebook رفض المحتوى لأنه مكرر. الـ AI سيعيد صياغته.',
            'action'   => 'Rewrite & Retry',
        ],
    ];

    public function analyze(ScheduledPost $post): JsonResponse
    {
        if ($post->user_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if ($post->status !== 'failed') {
            return response()->json(['error' => 'Post is not failed.'], 422);
        }

        $errorMsg = strtolower($post->error_message ?? '');
        $diagnosis = $this->diagnose($errorMsg);

        return response()->json([
            'post_id'   => $post->id,
            'error'     => $post->error_message,
            'diagnosis' => $diagnosis,
            'content'   => $post->content,
        ]);
    }


    public function fix(Request $request, ScheduledPost $post): JsonResponse
    {
        if ($post->user_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if ($post->status !== 'failed') {
            return response()->json(['error' => 'Post is not failed.'], 422);
        }

        $fix = $request->input('fix');

        return match ($fix) {
            'reconnect_page'  => $this->fixReconnectPage($post),
            'rewrite_content' => $this->fixRewriteContent($post),
            'remove_media'    => $this->fixRemoveMedia($post),
            'delay_post'      => $this->fixDelayPost($post),
            default           => response()->json(['error' => 'Unknown fix type.'], 422),
        };
    }

    // ── الحلول ───────────────────────────────────────────────────────────────
    private function fixReconnectPage(ScheduledPost $post): JsonResponse
    {
        return response()->json([
            'success'  => false,
            'redirect' => route('facebook.redirect'),
            'message'  => 'يجب إعادة ربط الصفحة بـ Facebook أولاً.',
        ]);
    }

    private function fixRewriteContent(ScheduledPost $post): JsonResponse
    {
        try {
            $response = Http::withHeaders(['Content-Type' => 'application/json'])
                ->post('https://api.anthropic.com/v1/messages', [
                    'model'      => 'claude-sonnet-4-20250514',
                    'max_tokens' => 500,
                    'messages'   => [[
                        'role'    => 'user',
                        'content' => "Rewrite this Facebook post to comply with Facebook's community standards. Make it engaging but safe. Return ONLY the rewritten post text, nothing else.\n\nOriginal post:\n{$post->content}",
                    ]],
                ]);

            $newContent = $response->json('content.0.text') ?? $post->content;

            $post->update([
                'content'       => trim($newContent),
                'status'        => 'pending',
                'error_message' => null,
                'scheduled_at'  => now()->addMinutes(5),
            ]);


            return response()->json([
                'success'     => true,
                'new_content' => $post->fresh()->content,
                'message'     => 'تمت إعادة صياغة المحتوى وجدولته بعد 5 دقائق.',
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => 'AI rewrite failed: ' . $e->getMessage()], 500);
        }
    }

    private function fixRemoveMedia(ScheduledPost $post): JsonResponse
    {
        $post->update([
            'media'         => null,
            'status'        => 'pending',
            'error_message' => null,
            'scheduled_at'  => now()->addMinutes(2),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'تمت إزالة الميديا وجدولة المنشور كنص فقط بعد دقيقتين.',
        ]);
    }

    private function fixDelayPost(ScheduledPost $post): JsonResponse
    {
        $post->update([
            'status'        => 'pending',
            'error_message' => null,
            'scheduled_at'  => now()->addHours(2),
        ]);

        return response()->json([
            'success'      => true,
            'scheduled_at' => $post->fresh()->scheduled_at->format('g:i A'),
            'message'      => 'تمت إعادة الجدولة بعد ساعتين.',
        ]);
    }

public function updateToken(Request $request, ScheduledPost $post): JsonResponse
{
    if ($post->user_id !== auth()->id()) {
        return response()->json(['error' => 'Unauthorized'], 403);
    }

    $request->validate([
        'page_access_token' => 'required|string|min:20',
    ]);

  
    $page = $post->facebookPage;

    if (!$page) {
        return response()->json(['error' => 'Page not found'], 404);
    }

    $page->update([
        'access_token'    => $request->page_access_token,
        'token_expires_at'=> now()->addDays(60),
        'is_active'       => true,
    ]);

    $post->update([
        'status'        => 'pending',
        'error_message' => null,
        'scheduled_at'  => now()->addMinutes(2),
    ]);

    return response()->json([
        'success' => true,
        'message' => 'Token updated! Post rescheduled in 2 minutes.',
    ]);
}
    private function diagnose(string $errorMsg): array
    {
        foreach ($this->errorPatterns as $key => $config) {
            foreach ($config['patterns'] as $pattern) {
                if (str_contains($errorMsg, $pattern)) {
                    return [
                        'type'    => $config['type'],
                        'title'   => $config['title'],
                        'message' => $config['message'],
                        'fix'     => $config['fix'],
                        'action'  => $config['action'],
                    ];
                }
            }
        }

     
        return [
            'type'    => 'unknown',
            'title'   => 'Unknown Error',
            'message' => 'خطأ غير معروف. سيحاول الـ AI إعادة صياغة المحتوى وإعادة النشر.',
            'fix'     => 'rewrite_content',
            'action'  => 'Rewrite & Retry',
        ];
    }
}