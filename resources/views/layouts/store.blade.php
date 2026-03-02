@extends('layouts.app')

@section('body')
<header class="border-b bg-white">
    <div class="mx-auto flex max-w-7xl items-center justify-between p-4">
        <a href="{{ route('home') }}" class="text-xl font-bold">PREMIUM STORE</a>
        <nav class="space-x-4">
            <a href="{{ route('products.index') }}">Shop</a>
            <a href="{{ route('cart.index') }}">Cart</a>
            @auth
                <a href="{{ route('account.orders.index') }}">Orders</a>
            @endauth
        </nav>
    </div>
</header>
<main class="mx-auto max-w-7xl p-6">@yield('content')</main>
@endsection
