<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $products = Product::query()->active()->with(['images', 'categories'])
            ->when($request->filled('category'), function ($query) use ($request) {
                $query->whereHas('categories', fn ($q) => $q->where('slug', $request->string('category')));
            })
            ->when($request->filled('min_price'), fn ($q) => $q->where('price', '>=', $request->float('min_price')))
            ->when($request->filled('max_price'), fn ($q) => $q->where('price', '<=', $request->float('max_price')))
            ->orderBy('created_at', 'desc')
            ->paginate(12)
            ->withQueryString();

        $categories = Category::where('is_active', true)->orderBy('sort_order')->get();

        return view('store.products.index', compact('products', 'categories'));
    }

    public function show(Product $product)
    {
        abort_unless($product->status === 'active', 404);

        return view('store.products.show', [
            'product' => $product->load(['images', 'categories', 'variants']),
        ]);
    }
}
