@extends('layouts.app')

@section('body')
<div class="min-h-screen grid grid-cols-[220px_1fr]">
    <aside class="bg-black text-white p-4 space-y-3">
        <h2 class="font-bold">Admin</h2>
        <a class="block" href="{{ route('admin.dashboard') }}">Dashboard</a>
        <a class="block" href="{{ route('admin.products.index') }}">Products</a>
        <a class="block" href="{{ route('admin.categories.index') }}">Categories</a>
        <a class="block" href="{{ route('admin.orders.index') }}">Orders</a>
    </aside>
    <main class="p-6">@yield('content')</main>
</div>
@endsection
