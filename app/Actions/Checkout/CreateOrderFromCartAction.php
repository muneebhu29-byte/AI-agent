<?php

namespace App\Actions\Checkout;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Cart;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CreateOrderFromCartAction
{
    public function execute(Cart $cart): Order
    {
        return DB::transaction(function () use ($cart) {
            $subtotal = $cart->items->sum(fn ($item) => $item->unit_price * $item->quantity);

            $order = Order::create([
                'order_number' => 'ORD-'.strtoupper(Str::random(10)),
                'user_id' => $cart->user_id,
                'status' => OrderStatus::Pending,
                'subtotal' => $subtotal,
                'discount_total' => 0,
                'tax_total' => 0,
                'shipping_total' => 0,
                'grand_total' => $subtotal,
                'currency' => 'USD',
                'payment_status' => PaymentStatus::Unpaid,
            ]);

            foreach ($cart->items as $item) {
                $order->items()->create([
                    'product_id' => $item->product_id,
                    'product_variant_id' => $item->product_variant_id,
                    'product_name_snapshot' => $item->product->name,
                    'sku_snapshot' => $item->variant?->sku ?? $item->product->sku,
                    'unit_price' => $item->unit_price,
                    'quantity' => $item->quantity,
                    'line_total' => $item->unit_price * $item->quantity,
                ]);
            }

            $cart->update(['status' => 'converted']);

            return $order->load('items');
        });
    }
}
