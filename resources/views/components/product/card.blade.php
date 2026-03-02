<div class="rounded border bg-white p-4 shadow-sm">
    <h3 class="font-semibold">{{ $product->name }}</h3>
    <p class="text-sm text-gray-600">${{ number_format($product->price, 2) }}</p>
    <a class="mt-2 inline-block underline" href="{{ route('products.show', $product) }}">View</a>
</div>
