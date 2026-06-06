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

    // ─── Generate 3 Captions ─────────────────────────────────────────────────

    public function generateCaption(Request $request)
    {
        $request->validate([
            // يقبل product_id أو بيانات المنتج مباشرة من الـ view
            'product_id'   => 'nullable|integer|exists:salla_products,id',
            'product_name' => 'required_without:product_id|string',
            'tone'         => 'nullable|in:promotional,friendly,urgent,elegant,exciting,formal,humorous',
            'language'     => 'nullable|in:ar,en',
            'goal'         => 'nullable|in:sales,awareness,engagement',
        ]);

        // ── جلب بيانات المنتج: إما من DB أو من الـ request مباشرة ──────────
        if ($request->filled('product_id')) {
            $product = SallaProduct::findOrFail($request->product_id);

            if ($product->account->user_id !== auth()->id()) {
                return response()->json(['error' => 'Unauthorized.'], 403);
            }

            $name        = $product->name;
            $description = $product->description;
            $price       = $product->price;
            $salePrice   = $product->sale_price;
            $currency    = $product->currency ?? 'SAR';
            $imageUrl    = $product->image_url;
            $productUrl  = $product->product_url;
            $hasDiscount = $product->hasDiscount();
            $discount    = $hasDiscount ? $product->discountPercent() : 0;
        } else {
            // الـ view بعث البيانات مباشرة بدون product_id
            $name        = $request->product_name;
            $description = $request->description;
            $price       = $request->price;
            $salePrice   = $request->sale_price;
            $currency    = $request->currency ?? 'SAR';
            $imageUrl    = $request->image_url;
            $productUrl  = $request->product_url;
            $hasDiscount = $salePrice && $price && $salePrice < $price;
            $discount    = $hasDiscount ? (int) round(($price - $salePrice) / $price * 100) : 0;
        }

        $tone = $request->get('tone', 'promotional');
        $lang = $request->get('language', 'ar') === 'ar' ? 'Arabic' : 'English';
        $goal = $request->get('goal', 'sales');

        $priceText = $hasDiscount
            ? "Sale price: {$salePrice} {$currency} (was {$price}, {$discount}% off)"
            : "Price: {$price} {$currency}";

        $urlLine = $productUrl ? "\nProduct URL: {$productUrl}" : '';

        $prompt = "You are a social media expert for Saudi e-commerce.
Write 3 different Facebook captions in {$lang} for this product.
Product: {$name}
{$priceText}
Description: " . Str::limit($description ?? '', 300) . "
Tone: {$tone}
Goal: {$goal}{$urlLine}
Rules:
- {$lang} ONLY
- 60-150 words each caption
- Use emojis naturally
- Clear call-to-action
- 5 relevant hashtags at the end of EACH caption
- Each caption must have a completely different hook and angle
Return ONLY valid JSON with no markdown:
{\"captions\":[\"caption1 with hashtags\",\"caption2 with hashtags\",\"caption3 with hashtags\"]}";

        try {
            $raw    = $this->gemini->generate($prompt);
            $clean  = preg_replace('/^```json\s*|```\s*$/m', '', trim($raw));
            $parsed = json_decode($clean, true);

            if (empty($parsed['captions']) || ! is_array($parsed['captions'])) {
                return response()->json(['error' => 'AI generation failed. Try again.'], 500);
            }

            return response()->json([
                'captions' => array_values($parsed['captions']),
                'product'  => [
                    'name'       => $name,
                    'price'      => $price,
                    'sale_price' => $salePrice,
                    'currency'   => $currency,
                    'image_url'  => $imageUrl,
                    'url'        => $productUrl,
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Generation failed. Please try again.'], 500);
        }
    }

    // ─── Schedule or Publish ──────────────────────────────────────────────────

    public function schedulePost(Request $request)
    {
        $request->validate([
            'facebook_page_id' => 'required|exists:facebook_pages,id',
            'content'          => 'required|string|max:63206',
            'scheduled_at'     => 'required|date|after:now',
            // product_id اختياري — الـ view قد يبعث image_url مباشرة
            'product_id'       => 'nullable|integer|exists:salla_products,id',
            'image_url'        => 'nullable|url',
        ]);

        $user = auth()->user();

        if (! $user->canSchedulePost()) {
            return response()->json(['error' => 'Post limit reached.'], 422);
        }

        $page = $user->facebookPages()->findOrFail($request->facebook_page_id);

        // تحديد صورة المنشور
        if ($request->filled('product_id')) {
            $product = SallaProduct::findOrFail($request->product_id);
            if ($product->account->user_id !== $user->id) {
                return response()->json(['error' => 'Unauthorized.'], 403);
            }
            $mediaUrl = $product->image_url;
        } else {
            $mediaUrl = $request->image_url;
        }

        $scheduledAt = Carbon::parse($request->scheduled_at);

        $post = ScheduledPost::create([
            'user_id'          => $user->id,
            'facebook_page_id' => $page->id,
            'content'          => $request->content,
            'media'            => $mediaUrl ? [['type' => 'image', 'url' => $mediaUrl]] : null,
            'scheduled_at'     => $scheduledAt,
            'status'           => 'pending',
            'post_type'        => 'promotional',
        ]);

        PublishPostJob::dispatch($post)->delay(now()->diffInSeconds($scheduledAt));

        return response()->json([
            'success' => true,
            'message' => 'تم جدولة المنشور! 🎉',
            'post_id' => $post->id,
        ]);
    }
}