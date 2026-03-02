<?php

namespace App\Http\Controllers\Store;

use App\Actions\Checkout\CreateOrderFromCartAction;
use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Services\Payments\StripeService;
use Illuminate\Http\Request;

class CheckoutController extends Controller
{
    public function index(Request $request)
    {
        $cart = Cart::where('user_id', $request->user()->id)->where('status', 'active')->with('items.product')->firstOrFail();

        return view('store.checkout.index', compact('cart'));
    }

    public function createStripeSession(
        Request $request,
        CreateOrderFromCartAction $createOrderFromCart,
        StripeService $stripeService
    ) {
        $cart = Cart::where('user_id', $request->user()->id)->where('status', 'active')->with('items.product', 'items.variant')->firstOrFail();
        abort_if($cart->items->isEmpty(), 422, 'Cart is empty.');

        $order = $createOrderFromCart->execute($cart);
        $session = $stripeService->createCheckoutSession(
            $order,
            route('checkout.index').'?success=1',
            route('checkout.index').'?cancel=1'
        );

        $order->payments()->create([
            'provider' => 'stripe',
            'provider_checkout_session_id' => $session->id,
            'amount' => $order->grand_total,
            'currency' => $order->currency,
            'status' => 'pending',
        ]);

        return redirect()->away($session->url);
    }
}
