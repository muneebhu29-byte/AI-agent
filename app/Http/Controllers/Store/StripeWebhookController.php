<?php

namespace App\Http\Controllers\Store;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Webhook;

class StripeWebhookController extends Controller
{
    public function __invoke(Request $request)
    {
        try {
            $event = Webhook::constructEvent(
                $request->getContent(),
                $request->header('Stripe-Signature', ''),
                (string) config('services.stripe.webhook_secret')
            );
        } catch (SignatureVerificationException $exception) {
            return response()->json(['message' => 'Invalid signature'], 400);
        }

        if ($event->type === 'checkout.session.completed') {
            DB::transaction(function () use ($event) {
                $session = $event->data->object;
                $payment = Payment::where('provider_checkout_session_id', $session->id)->first();

                if (! $payment || $payment->status === 'paid') {
                    return;
                }

                $payment->update([
                    'provider_payment_id' => $session->payment_intent ?? null,
                    'status' => 'paid',
                    'paid_at' => now(),
                    'raw_payload' => (array) $session,
                ]);

                $payment->order->update([
                    'status' => OrderStatus::Paid,
                    'payment_status' => PaymentStatus::Paid,
                    'placed_at' => now(),
                ]);
            });
        }

        return response()->json(['received' => true]);
    }
}
