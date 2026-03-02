@extends('layouts.admin')

@section('title', $product->exists ? 'Edit Product' : 'Create Product')

@section('content')
<form method="POST" action="{{ $product->exists ? route('admin.products.update', $product) : route('admin.products.store') }}" class="space-y-3 max-w-xl">
    @csrf
    @if($product->exists) @method('PUT') @endif
    <input class="w-full border p-2" name="name" placeholder="Name" value="{{ old('name', $product->name) }}">
    <input class="w-full border p-2" name="slug" placeholder="Slug" value="{{ old('slug', $product->slug) }}">
    <input class="w-full border p-2" name="sku" placeholder="SKU" value="{{ old('sku', $product->sku) }}">
    <input class="w-full border p-2" type="number" step="0.01" name="price" placeholder="Price" value="{{ old('price', $product->price) }}">
    <input class="w-full border p-2" type="number" name="stock_qty" placeholder="Stock" value="{{ old('stock_qty', $product->stock_qty) }}">
    <select name="status" class="w-full border p-2">
        @foreach(['draft','active','archived'] as $status)
            <option value="{{ $status }}" @selected(old('status', $product->status) === $status)>{{ ucfirst($status) }}</option>
        @endforeach
    </select>
    <x-ui.button>Save</x-ui.button>
</form>
@endsection
