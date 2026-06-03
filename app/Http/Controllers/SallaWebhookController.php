<?php
namespace App\Http\Controllers;

use App\Jobs\PublishPostJob;
use App\Models\SallaAccount;
use App\Models\SallaProduct;
use App\Models\ScheduledPost;
use App\Services\GeminiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SallaWebhookController extends Controller
{
    public function __construct(private readonly GeminiService $gemini) {}

    public function handle(Request $request)
    {
        // Signature verification
        $secret    = config('services.salla.webhook_secret');
        $signature = $request->header('X-Salla-Signature');
        if ($secret && $signature) {
            $expected = hash_hmac('sha256', $request->getContent(), $secret);
            if (!hash_equals($expected, $signature)) return response('Unauthorized', 401);
        }

        $event   = $request->input('event');
        $payload = $request->input('data', []);

        match ($event) {
            'product.created' => $this->upsertProduct($payload, autoPost: true),
            'product.updated' => $this->upsertProduct($payload, autoPost: false),
            'product.deleted' => $this->deleteProduct($payload),
            default           => null,
        };

        return response('OK', 200);
    }

    private function upsertProduct(array $payload, bool $autoPost): void
    {
        $account = SallaAccount::where('salla_merchant_id', (string) ($payload['merchant'] ?? ''))->first();
        if (!$account) return;

        $product = SallaProduct::updateOrCreate(
            ['salla_account_id' => $account->id, 'salla_product_id' => (string) $payload['id']],
            [
                'name'        => $payload['name'] ?? '',
                'description' => strip_tags($payload['description'] ?? ''),
                'price'       => $payload['price']['amount'] ?? null,
                'currency'    => $payload['price']['currency'] ?? 'SAR',
                'sale_price'  => $payload['sale_price']['amount'] ?? null,
                'image_url'   => $payload['images'][0]['url'] ?? null,
                'product_url' => $payload['url'] ?? null,
                'status'      => $payload['status'] ?? 'active',
            ]
        );

        if ($autoPost && $account->auto_post_enabled) {
            $this->autoSchedulePost($account, $payload);
        }
    }

    private function deleteProduct(array $payload): void
    {
        $account = SallaAccount::where('salla_merchant_id', (string) ($payload['merchant'] ?? ''))->first();
        if (!$account) return;
        SallaProduct::where('salla_account_id', $account->id)
            ->where('salla_product_id', (string) $payload['id'])->delete();
    }

    private function autoSchedulePost(SallaAccount $account, array $payload): void
    {
        $user = $account->user;
        if (!$user->hasActivePlan() || $user->facebookPages->isEmpty() || $user->remainingPostsCount() <= 0) return;

        try {
            $price  = ($payload['price']['amount'] ?? '') . ' ' . ($payload['price']['currency'] ?? 'SAR');
            $prompt = "Write a short Arabic Facebook post (60-100 words) for a new product.
Product: {$payload['name']}
Price: {$price}
Rules: Arabic only, emojis, CTA (اطلب الآن), 3 hashtags.
Return ONLY JSON: {\"content\":\"...\"}";

            $decoded = json_decode($this->gemini->generate($prompt), true);
            if (empty($decoded['content'])) return;

            $scheduledAt = now()->addHour();
            $post = ScheduledPost::create([
                'user_id'          => $user->id,
                'facebook_page_id' => $user->facebookPages->first()->id,
                'content'          => Str::limit($decoded['content'], 2000),
                'media'            => isset($payload['images'][0]['url']) ? [['type' => 'image', 'url' => $payload['images'][0]['url']]] : null,
                'scheduled_at'     => $scheduledAt,
                'status'           => 'pending',
                'post_type'        => 'promotional',
            ]);
            PublishPostJob::dispatch($post)->delay(now()->diffInSeconds($scheduledAt));
        } catch (\Exception $e) {
            Log::error('Salla auto-post failed: ' . $e->getMessage());
        }
    }
}