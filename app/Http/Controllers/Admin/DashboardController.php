<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;

class DashboardController extends Controller
{
    public function __invoke()
    {
        $metrics = [
            'orders' => Order::count(),
            'revenue' => (float) Order::where('payment_status', 'paid')->sum('grand_total'),
            'low_stock' => Product::where('stock_qty', '<=', 5)->count(),
        ];

        return view('admin.dashboard', compact('metrics'));
    }
}
