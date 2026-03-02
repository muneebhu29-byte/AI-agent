@extends('layouts.store')

@section('title', 'Home')

@section('content')
<h1 class="mb-6 text-3xl font-bold">Premium Performance Collection</h1>
<div class="grid gap-4 md:grid-cols-4">
    @foreach($featuredProducts as $product)
        <x-product.card :product="$product" />
    @endforeach
</div>
@endsection
