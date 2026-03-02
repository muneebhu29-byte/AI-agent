<?php

namespace App\Services\Payments;

use App\Models\Order;
use Stripe\Checkout\Session;
use Stripe\StripeClient;

class StripeService
{
    public function __construct(private readonly StripeClient $client)
    {
    }

    public function createCheckoutSession(Order $order, string $successUrl, string $cancelUrl): Session
    {
        return $this->client->checkout->sessions->create([
            'mode' => 'payment',
            'success_url' => $successUrl,
            'cancel_url' => $cancelUrl,
            'metadata' => [
                'order_id' => (string) $order->id,
                'order_number' => $order->order_number,
            ],
            'line_items' => $order->items->map(fn ($item) => [
                'price_data' => [
                    'currency' => strtolower($order->currency),
                    'unit_amount' => (int) round($item->unit_price * 100),
                    'product_data' => ['name' => $item->product_name_snapshot],
                ],
                'quantity' => $item->quantity,
            ])->toArray(),
        ]);
    }
}
