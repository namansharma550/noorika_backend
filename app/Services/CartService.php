<?php

namespace App\Services;

use App\Models\Cart;
use Illuminate\Http\Request;

class CartService
{
    public function resolve(Request $request): Cart
    {
        if ($request->user()) {
            return Cart::firstOrCreate(['user_id' => $request->user()->id]);
        }

        $sessionId = $request->header('X-Guest-Session');

        if (! $sessionId) {
            abort(422, 'Missing X-Guest-Session header for guest cart operations.');
        }

        return Cart::firstOrCreate(['session_id' => $sessionId, 'user_id' => null]);
    }

    public function mergeGuestCartIntoUser(string $sessionId, int $userId): void
    {
        $guestCart = Cart::where('session_id', $sessionId)->whereNull('user_id')->first();

        if (! $guestCart) {
            return;
        }

        $userCart = Cart::firstOrCreate(['user_id' => $userId]);

        foreach ($guestCart->items as $item) {
            $existing = $userCart->items()
                ->where('product_id', $item->product_id)
                ->where('variant_id', $item->variant_id)
                ->first();

            if ($existing) {
                $existing->increment('quantity', $item->quantity);
            } else {
                $userCart->items()->create([
                    'product_id' => $item->product_id,
                    'variant_id' => $item->variant_id,
                    'quantity' => $item->quantity,
                    'price_at_add' => $item->price_at_add,
                ]);
            }
        }

        $guestCart->delete();
    }

    public function totals(Cart $cart): array
    {
        $subtotal = $cart->items->sum(fn ($item) => $item->price_at_add * $item->quantity);

        return [
            'subtotal' => round($subtotal, 2),
            'item_count' => $cart->items->sum('quantity'),
        ];
    }
}
