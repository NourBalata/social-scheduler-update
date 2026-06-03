<?php
namespace App\Http\Controllers;

use App\Jobs\PublishPostJob;
use App\Models\SallaProduct;
use App\Models\ScheduledPost;
use App\Services\GeminiService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SallaPostController extends Controller
{
    public function __construct(private readonly GeminiService $gemini) {}

    public function generateCaption(Request $request)
    {
        $request->validate([
            'product_id' => 'required|integer|exists:salla_products,id',
            'tone'       => 'nullable|in:promotional,friendly,urgent,elegant',
            'language'   => 'nullable|in:ar,en',
        ]);

        $product = SallaProduct::findOrFail($request->product_id);
        if ($product->account->user_id !== auth()->id())
            return response()->json(['error' => 'Unauthorized.'], 403);

        $tone    = $request->get('tone', 'promotional');
        $lang    = $request->get('language', 'ar') === 'ar' ? 'Arabic' : 'English';
        $price   = $product->hasDiscount()
            ? "Sale: {$product->sale_price} {$product->currency} (was {$product->price}, {$product->discountPercent()}% off)"
            : "Price: {$product->price} {$product->currency}";

        $prompt = "You are a social media expert for Saudi e-commerce.
Write 3 Facebook captions in {$lang} for this product.
Product: {$product->name}
{$price}
Description: " . Str::limit($product->description, 300) . "
Tone: {$tone}
Rules: {$lang} ONLY, 60-150 words, emojis, clear CTA, include link if available, 5 hashtags.
Return ONLY valid JSON: {\"captions\":[\"...\",\"...\",\"...\"],\"hashtags\":[\"#tag1\",...]}";

        try {
            $parsed = json_decode($this->gemini->generate($prompt), true);
            if (!isset($parsed['captions'])) return response()->json(['error' => 'AI error.'], 500);
            return response()->json([
                'captions' => $parsed['captions'],
                'hashtags' => $parsed['hashtags'] ?? [],
                'product'  => ['name' => $product->name, 'price' => $product->price,
                               'currency' => $product->currency, 'sale_price' => $product->sale_price,
                               'image_url' => $product->image_url, 'url' => $product->product_url],
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Generation failed.'], 500);
        }
    }

    public function schedulePost(Request $request)
    {
        $request->validate([
            'product_id'       => 'required|integer|exists:salla_products,id',
            'facebook_page_id' => 'required|exists:facebook_pages,id',
            'content'          => 'required|string|max:63206',
            'scheduled_at'     => 'required|date|after:now',
        ]);

        $user    = auth()->user();
        $product = SallaProduct::findOrFail($request->product_id);
        if ($product->account->user_id !== $user->id) return response()->json(['error' => 'Unauthorized.'], 403);
        if (!$user->canSchedulePost()) return response()->json(['error' => 'Post limit reached.'], 422);

        $page        = $user->facebookPages()->findOrFail($request->facebook_page_id);
        $scheduledAt = Carbon::parse($request->scheduled_at);

        $post = ScheduledPost::create([
            'user_id'          => $user->id,
            'facebook_page_id' => $page->id,
            'content'          => $request->content,
            'media'            => $product->image_url ? [['type' => 'image', 'url' => $product->image_url]] : null,
            'scheduled_at'     => $scheduledAt,
            'status'           => 'pending',
            'post_type'        => 'promotional',
        ]);

        PublishPostJob::dispatch($post)->delay(now()->diffInSeconds($scheduledAt));

        return response()->json(['success' => true, 'message' => 'تم جدولة المنشور! 🎉', 'post_id' => $post->id]);
    }
}