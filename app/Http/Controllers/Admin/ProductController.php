<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ProductRequest;
use App\Models\Category;
use App\Models\Product;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::with('categories')->latest()->paginate(15);

        return view('admin.products.index', compact('products'));
    }

    public function create()
    {
        return view('admin.products.form', ['product' => new Product(), 'categories' => Category::all()]);
    }

    public function store(ProductRequest $request)
    {
        $product = Product::create($request->validated());
        $product->categories()->sync($request->input('category_ids', []));

        return redirect()->route('admin.products.index')->with('success', 'Product created');
    }

    public function edit(Product $product)
    {
        return view('admin.products.form', ['product' => $product, 'categories' => Category::all()]);
    }

    public function update(ProductRequest $request, Product $product)
    {
        $product->update($request->validated());
        $product->categories()->sync($request->input('category_ids', []));

        return redirect()->route('admin.products.index')->with('success', 'Product updated');
    }

    public function destroy(Product $product)
    {
        $product->delete();

        return redirect()->route('admin.products.index')->with('success', 'Product archived');
    }
}
