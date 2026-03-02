@extends('layouts.store')

@section('title', $product->name)

@section('content')
<h1 class="text-3xl font-bold">{{ $product->name }}</h1>
<p class="text-gray-600">{{ $product->short_description }}</p>
<p class="my-4 text-xl font-semibold">${{ number_format($product->price, 2) }}</p>
<form method="POST" action="{{ route('cart.items.store') }}">
    @csrf
    <input type="hidden" name="product_id" value="{{ $product->id }}">
    <input type="number" name="quantity" value="1" min="1" class="border p-2">
    <x-ui.button>Add to Cart</x-ui.button>
</form>
@endsection
