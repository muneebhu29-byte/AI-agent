@extends('layouts.store')

@section('title', 'Products')

@section('content')
<h1 class="mb-4 text-2xl font-bold">Products</h1>
<div class="grid gap-4 md:grid-cols-4">
    @foreach($products as $product)
        <x-product.card :product="$product" />
    @endforeach
</div>
<div class="mt-4">{{ $products->links() }}</div>
@endsection
