<?php

namespace App\Services;

use App\Models\Plan;
use App\Models\SubscriptionInvoice;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Stripe\Customer;
use Stripe\Exception\ApiErrorException;
use Stripe\Stripe;
use Stripe\StripeClient;

class StripeService
{
private ?StripeClient $stripe;

public function __construct()
{
    $secret = config('services.stripe.secret');
    
    $this->stripe = ($secret && str_starts_with($secret, 'sk_'))
        ? new StripeClient($secret)
        : null;
}

    // ─── Customer ─────────────────────────────────────────────────────────────

    /**
     * ارجع customer_id الموجود أو أنشئ واحد جديد.
     */
    public function resolveCustomer(User $user): string
    {
        if ($user->hasStripeCustomer()) {
            return $user->stripe_customer_id;
        }

        $customer = $this->stripe->customers->create([
            'email'    => $user->email,
            'name'     => $user->name,
            'metadata' => ['user_id' => $user->id],
        ]);

        $user->update(['stripe_customer_id' => $customer->id]);

        Log::info("Stripe customer created", [
            'user_id'     => $user->id,
            'customer_id' => $customer->id,
        ]);

        return $customer->id;
    }

    // ─── Checkout ─────────────────────────────────────────────────────────────

    /**
     * أنشئ Stripe Checkout Session وارجع الـ URL.
     */
    public function createCheckoutSession(User $user, Plan $plan): string
    {
        if (! $plan->hasStripePrice()) {
            throw new \Exception("Plan [{$plan->slug}] has no stripe_price_id configured.");
        }

        $customerId = $this->resolveCustomer($user);

        $session = $this->stripe->checkout->sessions->create([
            'customer'            => $customerId,
            'mode'                => 'subscription',
            'payment_method_types' => ['card'],
            'line_items'          => [[
                'price'    => $plan->stripe_price_id,
                'quantity' => 1,
            ]],
            'success_url' => route('billing.success') . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url'  => route('plans.index'),
            'metadata'    => [
                'user_id' => $user->id,
                'plan_id' => $plan->id,
            ],
            'subscription_data' => [
                'metadata' => [
                    'user_id' => $user->id,
                    'plan_id' => $plan->id,
                ],
            ],
            // إظهار تفاصيل الضريبة التلقائية (اختياري)
            // 'automatic_tax' => ['enabled' => true],
        ]);

        return $session->url;
    }

    // ─── Customer Portal ──────────────────────────────────────────────────────

    /**
     * Stripe Customer Portal — المستخدم يدير اشتراكه بنفسه (كرت، إلغاء، فواتير).
     */
    public function createPortalSession(User $user): string
    {
        if (! $user->hasStripeCustomer()) {
            throw new \Exception("User has no Stripe customer.");
        }

        $session = $this->stripe->billingPortal->sessions->create([
            'customer'   => $user->stripe_customer_id,
            'return_url' => route('plans.index'),
        ]);

        return $session->url;
    }

    // ─── Cancel ───────────────────────────────────────────────────────────────

    /**
     * إلغاء الاشتراك في نهاية الفترة الحالية (لا يقطع الخدمة فوراً).
     */
    public function cancelSubscription(User $user): void
    {
        if (! $user->stripe_subscription_id) {
            return;
        }

        $this->stripe->subscriptions->update($user->stripe_subscription_id, [
            'cancel_at_period_end' => true,
        ]);

        Log::info("Stripe subscription set to cancel at period end", [
            'user_id'         => $user->id,
            'subscription_id' => $user->stripe_subscription_id,
        ]);
    }

    /**
     * إلغاء فوري (للأدمن أو الحالات الخاصة).
     */
    public function cancelSubscriptionImmediately(User $user): void
    {
        if (! $user->stripe_subscription_id) {
            return;
        }

        $this->stripe->subscriptions->cancel($user->stripe_subscription_id);
    }

    // ─── Webhook Handlers ─────────────────────────────────────────────────────

    /**
     * تفعيل الاشتراك بعد نجاح الدفع — يُستدعى من webhook.
     */
    public function handleSubscriptionActivated(array $subscription): void
    {
        $userId = $subscription['metadata']['user_id'] ?? null;
        $planId = $subscription['metadata']['plan_id'] ?? null;

        if (! $userId || ! $planId) {
            Log::warning("Stripe webhook: missing metadata in subscription", $subscription);
            return;
        }

        $user = User::find($userId);
        $plan = Plan::find($planId);

        if (! $user || ! $plan) {
            Log::error("Stripe webhook: user or plan not found", compact('userId', 'planId'));
            return;
        }

        $user->update([
            'plan_id'                => $plan->id,
            'stripe_subscription_id' => $subscription['id'],
            'stripe_price_id'        => $subscription['items']['data'][0]['price']['id'] ?? null,
            'stripe_status'          => $subscription['status'],
            'plan_expires_at'        => null, // Stripe يتولى الـ renewal
        ]);

        Log::info("Subscription activated", [
            'user_id' => $user->id,
            'plan'    => $plan->slug,
        ]);
    }

    /**
     * تحديث status الاشتراك (past_due, canceled, etc).
     */
    public function handleSubscriptionUpdated(array $subscription): void
    {
        $user = User::where('stripe_subscription_id', $subscription['id'])->first();

        if (! $user) {
            return;
        }

        $updates = [
            'stripe_status' => $subscription['status'],
        ];

        // إذا انتهى الاشتراك ارجع للخطة المجانية
        if (in_array($subscription['status'], ['canceled', 'unpaid'])) {
            $freePlan = Plan::where('slug', 'free')->first();
            $updates['plan_id']                = $freePlan?->id;
            $updates['stripe_subscription_id'] = null;
            $updates['stripe_price_id']        = null;
        }

        $user->update($updates);

        Log::info("Subscription status updated", [
            'user_id' => $user->id,
            'status'  => $subscription['status'],
        ]);
    }

    /**
     * تسجيل الفاتورة بعد الدفع.
     */
    public function handleInvoicePaid(array $invoice): void
    {
        $user = User::where('stripe_customer_id', $invoice['customer'])->first();

        if (! $user) {
            return;
        }

        // ابحث عن الخطة من خلال stripe_price_id
        $priceId = $invoice['lines']['data'][0]['price']['id'] ?? null;
        $plan    = $priceId ? Plan::where('stripe_price_id', $priceId)->first() : null;

        if (! $plan) {
            return;
        }

        SubscriptionInvoice::updateOrCreate(
            ['stripe_invoice_id' => $invoice['id']],
            [
                'user_id'                  => $user->id,
                'plan_id'                  => $plan->id,
                'stripe_payment_intent_id' => $invoice['payment_intent'] ?? null,
                'status'                   => $invoice['status'],
                'amount'                   => $invoice['amount_paid'] / 100,
                'currency'                 => $invoice['currency'],
                'period_start'             => $invoice['lines']['data'][0]['period']['start']
                    ? \Carbon\Carbon::createFromTimestamp($invoice['lines']['data'][0]['period']['start'])
                    : null,
                'period_end'               => $invoice['lines']['data'][0]['period']['end']
                    ? \Carbon\Carbon::createFromTimestamp($invoice['lines']['data'][0]['period']['end'])
                    : null,
                'paid_at'                  => now(),
                'invoice_pdf_url'          => $invoice['invoice_pdf'] ?? null,
            ]
        );

        Log::info("Invoice recorded", [
            'user_id'    => $user->id,
            'invoice_id' => $invoice['id'],
            'amount'     => $invoice['amount_paid'] / 100,
        ]);
    }
}