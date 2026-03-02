@extends('layouts.admin')

@section('title', 'Products')

@section('content')
<div class="flex items-center justify-between mb-4">
    <h1 class="text-2xl font-bold">Products</h1>
    <a href="{{ route('admin.products.create') }}" class="rounded bg-black px-4 py-2 text-white">New Product</a>
</div>
<table class="w-full bg-white border">
    <tr><th class="p-2">Name</th><th>SKU</th><th>Status</th><th>Price</th><th></th></tr>
    @foreach($products as $product)
    <tr class="border-t">
        <td class="p-2">{{ $product->name }}</td>
        <td>{{ $product->sku }}</td>
        <td>{{ $product->status }}</td>
        <td>${{ number_format($product->price, 2) }}</td>
        <td><a class="underline" href="{{ route('admin.products.edit', $product) }}">Edit</a></td>
    </tr>
    @endforeach
</table>
<div class="mt-4">{{ $products->links() }}</div>
@endsection
