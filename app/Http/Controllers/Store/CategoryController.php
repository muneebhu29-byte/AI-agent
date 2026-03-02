<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Models\Category;

class CategoryController extends Controller
{
    public function show(Category $category)
    {
        $products = $category->products()->active()->with('images')->paginate(12);

        return view('store.products.index', compact('products', 'category'));
    }
}
