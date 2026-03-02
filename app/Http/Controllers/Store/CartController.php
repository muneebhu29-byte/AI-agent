<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Http\Requests\Store\AddToCartRequest;
use App\Models\Cart;
use App\Models\Product;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function index(Request $request)
    {
        $cart = $this->resolveCart($request);

        return view('store.cart.index', ['cart' => $cart?->load('items.product')]);
    }

    public function store(AddToCartRequest $request)
    {
        $cart = $this->resolveCart($request, true);
        $product = Product::findOrFail($request->integer('product_id'));

        $item = $cart->items()->firstOrNew([
            'product_id' => $product->id,
            'product_variant_id' => $request->input('product_variant_id'),
        ]);

        $item->quantity = ($item->quantity ?? 0) + $request->integer('quantity');
        $item->unit_price = $product->price;
        $item->save();

        return back()->with('success', 'Item added to cart.');
    }

    public function update(Request $request, int $item)
    {
        $validated = $request->validate(['quantity' => ['required', 'integer', 'min:1', 'max:10']]);
        $cart = $this->resolveCart($request, true);

        $cart->items()->whereKey($item)->update(['quantity' => $validated['quantity']]);

        return back()->with('success', 'Cart updated.');
    }

    public function destroy(Request $request, int $item)
    {
        $cart = $this->resolveCart($request, true);
        $cart->items()->whereKey($item)->delete();

        return back()->with('success', 'Item removed.');
    }

    private function resolveCart(Request $request, bool $create = false): ?Cart
    {
        $query = Cart::query()->where('status', 'active');

        if ($request->user()) {
            $query->where('user_id', $request->user()->id);
        } else {
            $query->where('session_id', $request->session()->getId());
        }

        if (! $create) {
            return $query->first();
        }

        return $query->firstOrCreate([
            'user_id' => $request->user()?->id,
            'session_id' => $request->user() ? null : $request->session()->getId(),
            'status' => 'active',
        ]);
    }
}
