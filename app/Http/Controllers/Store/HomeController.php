<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Models\Product;

class HomeController extends Controller
{
    public function __invoke()
    {
        $featuredProducts = Product::active()->where('is_featured', true)->with('images')->take(8)->get();

        return view('store.home', compact('featuredProducts'));
    }
}
