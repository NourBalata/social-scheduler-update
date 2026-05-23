<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Services\StripeService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Webhook;

class BillingController extends Controller
{
public function __construct(
    private readonly StripeService $stripe
) {}

    public function checkout(Plan $plan)
    {
        $user = auth()->user();

        if ($plan->isFree()) {
            $user->update([
                'plan_id'                => $plan->id,
                'stripe_subscription_id' => null,
                'stripe_price_id'        => null,
                'stripe_status'          => null,
                'plan_expires_at'        => null,
            ]);

            return redirect()->route('dashboard')
                ->with('success', 'Switched to Free plan.');
        }

        if (! $plan->hasStripePrice()) {
            return back()->with('error', 'This plan is not available for purchase yet.');
        }

        if ($user->hasActiveStripeSubscription() && $user->stripe_price_id === $plan->stripe_price_id) {
            return redirect()->route('billing.portal');
        }

        try {
            $url = $this->stripe->createCheckoutSession($user, $plan);
            return redirect()->away($url);
        } catch (\Exception $e) {
            Log::error('Stripe checkout failed', ['error' => $e->getMessage()]);
            return back()->with('error', 'Unable to start checkout. Please try again.');
        }
    }

    public function fakeCheckout(Plan $plan)
{
    try {
        $user = auth()->user();

        $user->update([
            'plan_id'         => $plan->id,
            'plan_expires_at' => now()->addMonth(),
            'stripe_status'   => 'active',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Plan activated successfully!',
        ]);

    } catch (\Exception $e) {
        Log::error('Fake checkout failed', ['error' => $e->getMessage()]);
        return response()->json([
            'success' => false,
            'message' => 'Something went wrong.',
        ], 500);
    }
}
    public function success(Request $request)
    {
        return redirect()->route('dashboard')
            ->with('success', 'Payment successful!');
    }

    public function portal()
    {
        $user = auth()->user();

        if (! $user->hasStripeCustomer()) {
            return back()->with('error', 'No billing account found.');
        }

        try {
            $url = $this->stripe->createPortalSession($user);
            return redirect()->away($url);
        } catch (\Exception $e) {
            Log::error('Stripe portal failed', ['error' => $e->getMessage()]);
            return back()->with('error', 'Unable to open billing portal.');
        }
    }

    public function cancel()
    {
        $user = auth()->user();

        if (! $user->hasActiveStripeSubscription()) {
            return back()->with('error', 'No active subscription to cancel.');
        }

        try {
            $this->stripe->cancelSubscription($user);
            return redirect()->route('plans.index')
                ->with('success', 'Your subscription will be cancelled at the end of the current billing period.');
        } catch (\Exception $e) {
            Log::error('Stripe cancel failed', ['error' => $e->getMessage()]);
            return back()->with('error', 'Unable to cancel subscription. Please try again.');
        }
    }

    public function invoices()
    {
        $invoices = auth()->user()->invoices()->paginate(10);
        return view('billing.invoices', compact('invoices'));
    }

    public function webhook(Request $request): Response
    {
        $payload   = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $secret    = config('services.stripe.webhook_secret');

        try {
            $event = Webhook::constructEvent($payload, $sigHeader, $secret);
        } catch (SignatureVerificationException $e) {
            Log::warning('Stripe webhook signature mismatch');
            return response('Invalid signature', 400);
        } catch (\Exception $e) {
            Log::error('Stripe webhook parse error', ['error' => $e->getMessage()]);
            return response('Webhook error', 400);
        }

        Log::info("Stripe webhook received: {$event->type}");

        match ($event->type) {
            'customer.subscription.created',
            'customer.subscription.updated'  => $this->stripe->handleSubscriptionUpdated($event->data->object->toArray()),
            'checkout.session.completed'     => $this->handleCheckoutCompleted($event->data->object->toArray()),
            'invoice.paid'                   => $this->stripe->handleInvoicePaid($event->data->object->toArray()),
            'customer.subscription.deleted'  => $this->stripe->handleSubscriptionUpdated(
                array_merge($event->data->object->toArray(), ['status' => 'canceled'])
            ),
            default => null,
        };

        return response('OK', 200);
    }

    private function handleCheckoutCompleted(array $session): void
    {
        if ($session['mode'] !== 'subscription' || empty($session['subscription'])) {
            return;
        }

        try {
            $stripeClient = new \Stripe\StripeClient(config('services.stripe.secret'));
            $subscription = $stripeClient->subscriptions->retrieve(
                $session['subscription'],
                ['expand' => ['items.data.price']]
            );

            $this->stripe->handleSubscriptionActivated($subscription->toArray());
        } catch (\Exception $e) {
            Log::error('handleCheckoutCompleted failed', ['error' => $e->getMessage()]);
        }
    }
}