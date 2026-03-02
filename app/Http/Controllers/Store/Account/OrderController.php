<?php

namespace App\Http\Controllers\Store\Account;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $orders = $request->user()->orders()->latest()->paginate(10);

        return view('store.account.orders.index', compact('orders'));
    }

    public function show(Request $request, Order $order)
    {
        abort_unless($order->user_id === $request->user()->id, 403);

        return view('store.account.orders.show', ['order' => $order->load('items', 'payments')]);
    }
}
