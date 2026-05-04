<?php

namespace App\Http\Controllers;

use App\Models\ContentPlan;
use App\Models\ContentPlanPost;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

class ContentPlanController extends Controller
{
    public function index()
    {
        $plans = auth()->user()->contentPlans()->with('posts')->latest()->get();
        return view('autopilot.index', compact('plans'));
    }

    public function generate(Request $request)
    {
        $request->validate([
            'page_name'      => 'required|string',
            'business_type'  => 'required|string',
            'audience'       => 'required|string',
            'tone'           => 'required|string',
            'language'       => 'required|in:ar,en',
            'start_date'     => 'required|date|after_or_equal:today',
            'posts_per_week' => 'required|integer|min:1|max:7',
        ]);

        // احسب عدد الأسابيع
        $start    = Carbon::parse($request->start_date);
        $end      = $start->copy()->addDays(29); // شهر كامل
        $weeks    = 4;
        $total    = $weeks * $request->posts_per_week;

        // اصنع الـ plan
        $plan = ContentPlan::create([
            'user_id'        => auth()->id(),
            'page_name'      => $request->page_name,
            'business_type'  => $request->business_type,
            'audience'       => $request->audience,
            'tone'           => $request->tone,
            'language'       => $request->language,
            'start_date'     => $start,
            'end_date'       => $end,
            'posts_per_week' => $request->posts_per_week,
            'status'         => 'generating',
        ]);

        // ولّد المحتوى بالـ AI
        $apiKey = config('services.gemini.key');

        $prompt = "
You are a professional social media content strategist.
Generate a {$total}-post content plan for ONE MONTH for the following business:

- Business Type: {$request->business_type}
- Target Audience: {$request->audience}
- Tone/Style: {$request->tone}
- Language: " . ($request->language === 'ar' ? 'Arabic' : 'English') . "
- Posts per week: {$request->posts_per_week}
- Start date: {$start->format('Y-m-d')}

Rules:
1. Mix post types: educational, entertainment, promotional, engagement
2. Each post must be engaging, include emojis and relevant hashtags
3. Distribute posts evenly across the month
4. Vary the content — no repetition

Return ONLY a valid JSON array, no explanation, no markdown. Format:
[
  {
    \"day_offset\": 0,
    \"post_type\": \"educational\",
    \"content\": \"post content here with emojis and hashtags\"
  }
]
";

        try {
            $response = Http::withHeaders(['Content-Type' => 'application/json'])
                ->timeout(60)
                ->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key={$apiKey}", [
                    'contents' => [['parts' => [['text' => $prompt]]]],
                    'generationConfig' => ['temperature' => 0.8, 'maxOutputTokens' => 8192]
                ]);

            if (!$response->successful()) {
                $plan->delete();
                return response()->json(['success' => false, 'error' => 'AI generation failed'], 500);
            }

            $raw  = $response->json('candidates.0.content.parts.0.text', '[]');
            $clean = preg_replace('/```json|```/', '', $raw);
            $posts = json_decode(trim($clean), true);

            if (!is_array($posts) || empty($posts)) {
                $plan->delete();
                return response()->json(['success' => false, 'error' => 'Invalid AI response'], 500);
            }

            // احفظ البوستات
            $bestHours = [9, 12, 18, 21];
            foreach ($posts as $index => $post) {
                $dayOffset   = $post['day_offset'] ?? ($index * (28 / $total));
                $hour        = $bestHours[$index % count($bestHours)];
                $scheduledAt = $start->copy()->addDays((int)$dayOffset)->setHour($hour)->setMinute(0);

                ContentPlanPost::create([
                    'content_plan_id' => $plan->id,
                    'page_name'       => $request->page_name,
                    'content'         => $post['content'],
                    'post_type'       => $post['post_type'] ?? 'educational',
                    'scheduled_at'    => $scheduledAt,
                    'status'          => 'pending',
                ]);
            }

            $plan->update(['status' => 'ready']);

            return response()->json([
                'success' => true,
                'plan_id' => $plan->id,
                'total'   => count($posts),
            ]);

        } catch (\Exception $e) {
            $plan->delete();
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function scheduleAll(Request $request, ContentPlan $plan)
    {
        // تأكد إن الـ plan تاعه
        if ($plan->user_id !== auth()->id()) abort(403);

        $scheduled = 0;
        foreach ($plan->posts()->where('status', 'pending')->get() as $planPost) {
            Post::create([
                'user_id'      => auth()->id(),
                'page_name'    => $planPost->page_name,
                'content'      => $planPost->content,
                'post_type'    => 'text',
                'scheduled_at' => $planPost->scheduled_at,
                'status'       => 'scheduled',
            ]);
            $planPost->update(['status' => 'scheduled']);
            $scheduled++;
        }

        $plan->update(['status' => 'scheduled']);

        return response()->json(['success' => true, 'scheduled' => $scheduled]);
    }

    public function destroy(ContentPlan $plan)
    {
        if ($plan->user_id !== auth()->id()) abort(403);
        $plan->delete();
        return response()->json(['success' => true]);
    }
}