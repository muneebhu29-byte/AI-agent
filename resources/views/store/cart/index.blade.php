@extends('layouts.store')

@section('title', 'Cart')

@section('content')
<h1 class="text-2xl font-bold mb-4">Your Cart</h1>
@if(!$cart || $cart->items->isEmpty())
    <p>Your cart is empty.</p>
@else
    <div class="space-y-3">
        @foreach($cart->items as $item)
            <div class="border p-3 rounded flex justify-between">
                <span>{{ $item->product->name }} x {{ $item->quantity }}</span>
                <span>${{ number_format($item->unit_price * $item->quantity, 2) }}</span>
            </div>
        @endforeach
    </div>
    @auth
    <a class="inline-block mt-4 rounded bg-black text-white px-4 py-2" href="{{ route('checkout.index') }}">Checkout</a>
    @endauth
@endif
@endsection
