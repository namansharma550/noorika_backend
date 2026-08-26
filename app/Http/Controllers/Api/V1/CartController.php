<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Models\Product;
use App\Services\CartService;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function __construct(protected CartService $cartService) {}

    public function show(Request $request)
    {
        $cart = $this->cartService->resolve($request);
        $cart->load(['items.product.images', 'items.variant']);

        return response()->json([
            'cart' => $cart,
            'totals' => $this->cartService->totals($cart),
        ]);
    }

    public function addItem(Request $request)
    {
        $data = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'variant_id' => ['nullable', 'exists:product_variants,id'],
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        $cart = $this->cartService->resolve($request);
        $product = Product::findOrFail($data['product_id']);

        $item = $cart->items()
            ->where('product_id', $product->id)
            ->where('variant_id', $data['variant_id'] ?? null)
            ->first();

        if ($item) {
            $item->increment('quantity', $data['quantity']);
        } else {
            $cart->items()->create([
                'product_id' => $product->id,
                'variant_id' => $data['variant_id'] ?? null,
                'quantity' => $data['quantity'],
                'price_at_add' => $product->price,
            ]);
        }

        $cart->load(['items.product.images', 'items.variant']);

        return response()->json([
            'cart' => $cart,
            'totals' => $this->cartService->totals($cart),
        ], 201);
    }

    public function updateItem(Request $request, int $itemId)
    {
        $data = $request->validate([
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        $cart = $this->cartService->resolve($request);
        $item = $cart->items()->findOrFail($itemId);
        $item->update(['quantity' => $data['quantity']]);

        $cart->load(['items.product.images', 'items.variant']);

        return response()->json([
            'cart' => $cart,
            'totals' => $this->cartService->totals($cart),
        ]);
    }

    public function removeItem(Request $request, int $itemId)
    {
        $cart = $this->cartService->resolve($request);
        $cart->items()->where('id', $itemId)->delete();

        $cart->load(['items.product.images', 'items.variant']);

        return response()->json([
            'cart' => $cart,
            'totals' => $this->cartService->totals($cart),
        ]);
    }

    public function applyCoupon(Request $request)
    {
        $data = $request->validate(['code' => ['required', 'string']]);

        $coupon = Coupon::where('code', $data['code'])
            ->where('status', 'active')
            ->where(fn ($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()))
            ->first();

        if (! $coupon) {
            return response()->json(['message' => 'Invalid or expired coupon.'], 422);
        }

        $cart = $this->cartService->resolve($request);
        $totals = $this->cartService->totals($cart);

        if ($totals['subtotal'] < $coupon->min_order_value) {
            return response()->json([
                'message' => "Minimum order value of {$coupon->min_order_value} required for this coupon.",
            ], 422);
        }

        $discount = $coupon->type === 'percent'
            ? round($totals['subtotal'] * $coupon->value / 100, 2)
            : $coupon->value;

        return response()->json([
            'coupon' => $coupon,
            'discount' => $discount,
            'totals' => $totals,
        ]);
    }
}
