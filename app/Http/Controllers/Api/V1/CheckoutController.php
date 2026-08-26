<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\Payment;
use App\Services\CartService;
use App\Services\Payments\PaymentGatewayFactory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class CheckoutController extends Controller
{
    public function __construct(
        protected CartService $cartService,
        protected PaymentGatewayFactory $gateways,
    ) {}

    /** Which payment methods the storefront should offer, driven by what an admin has configured in Settings. */
    public function paymentMethods()
    {
        return response()->json([
            'gateways' => $this->gateways->availableGateways(),
            'cod_available' => true,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            // A logged-in user may reference a saved address …
            'shipping_address_id' => ['required_without:shipping_address', 'nullable', 'integer', 'exists:addresses,id'],
            'billing_address_id' => ['nullable', 'integer', 'exists:addresses,id'],
            // … a guest (or a logged-in user checking out with a new address) supplies one inline instead.
            'shipping_address' => ['required_without:shipping_address_id', 'nullable', 'array'],
            'shipping_address.line1' => ['required_with:shipping_address', 'string', 'max:255'],
            'shipping_address.line2' => ['nullable', 'string', 'max:255'],
            'shipping_address.city' => ['required_with:shipping_address', 'string', 'max:100'],
            'shipping_address.state' => ['required_with:shipping_address', 'string', 'max:100'],
            'shipping_address.pincode' => ['required_with:shipping_address', 'string', 'max:20'],
            'shipping_address.country' => ['nullable', 'string', 'max:100'],
            'shipping_address.phone' => ['required_with:shipping_address', 'string', 'max:20'],
            'payment_method' => ['required', 'in:razorpay,stripe,cod,qr_manual'],
            'coupon_code' => ['nullable', 'string'],
        ]);

        if (! in_array($data['payment_method'], ['cod', 'qr_manual'], true)
            && ! in_array($data['payment_method'], $this->gateways->availableGateways(), true)) {
            return response()->json(['message' => 'This payment method is not currently available.'], 422);
        }

        if ($data['payment_method'] === 'qr_manual' && ! in_array('qr_manual', $this->gateways->availableGateways(), true)) {
            return response()->json(['message' => 'UPI QR payment is not currently available.'], 422);
        }

        // A user_id-owned address must belong to the requester — an id from someone
        // else's address book must never be usable just because it exists.
        if (! empty($data['shipping_address_id'])) {
            $owned = Address::where('id', $data['shipping_address_id'])
                ->where('user_id', $request->user()?->id)
                ->exists();
            if (! $owned) {
                return response()->json(['message' => 'Invalid shipping address.'], 422);
            }
        }

        $cart = $this->cartService->resolve($request);
        $cart->load('items.product');

        if ($cart->items->isEmpty()) {
            return response()->json(['message' => 'Cart is empty.'], 422);
        }

        $order = DB::transaction(function () use ($request, $data, $cart) {
            $shippingAddressId = $data['shipping_address_id']
                ?? Address::create([
                    'user_id' => $request->user()?->id,
                    ...$data['shipping_address'],
                ])->id;

            $totals = $this->cartService->totals($cart);
            $discount = 0;
            $coupon = null;

            if (! empty($data['coupon_code'])) {
                $coupon = Coupon::where('code', $data['coupon_code'])->where('status', 'active')->first();
                if ($coupon) {
                    $discount = $coupon->type === 'percent'
                        ? round($totals['subtotal'] * $coupon->value / 100, 2)
                        : (float) $coupon->value;
                }
            }

            $shippingFee = $totals['subtotal'] >= 999 ? 0 : 79;
            $tax = round(($totals['subtotal'] - $discount) * 0.03, 2);
            $total = round($totals['subtotal'] - $discount + $shippingFee + $tax, 2);

            $order = Order::create([
                'order_number' => 'NRK-'.strtoupper(Str::random(8)),
                'user_id' => $request->user()?->id,
                'status' => 'pending',
                'payment_status' => 'pending',
                'payment_method' => $data['payment_method'],
                'subtotal' => $totals['subtotal'],
                'discount' => $discount,
                'shipping_fee' => $shippingFee,
                'tax' => $tax,
                'total' => $total,
                'shipping_address_id' => $shippingAddressId,
                'billing_address_id' => $data['billing_address_id'] ?? $shippingAddressId,
                'coupon_id' => $coupon?->id,
                'placed_at' => now(),
            ]);

            foreach ($cart->items as $item) {
                $order->items()->create([
                    'product_id' => $item->product_id,
                    'variant_id' => $item->variant_id,
                    'product_name_snapshot' => $item->product->name,
                    'price_snapshot' => $item->price_at_add,
                    'quantity' => $item->quantity,
                    'subtotal' => $item->price_at_add * $item->quantity,
                ]);
            }

            $order->statusHistory()->create([
                'status' => 'pending',
                'note' => 'Order placed',
                'changed_by' => $request->user()?->id,
            ]);

            $coupon?->increment('used_count');

            $cart->items()->delete();

            return $order;
        });

        $order->load('items');

        // COD needs no gateway intent — it's confirmed on placement, paid on delivery.
        if ($data['payment_method'] === 'cod') {
            $order->update(['status' => 'confirmed']);
            $order->statusHistory()->create(['status' => 'confirmed', 'note' => 'Cash on delivery']);

            return response()->json(['order' => $order->fresh('items')], 201);
        }

        // QR/UPI needs no gateway intent either — the order stays pending until the
        // customer submits proof of payment and an admin manually verifies it.
        if ($data['payment_method'] === 'qr_manual') {
            return response()->json(['order' => $order->fresh('items')], 201);
        }

        try {
            $gateway = $this->gateways->make($data['payment_method']);
            $intent = $gateway->createIntent($order);
        } catch (RuntimeException $e) {
            return response()->json(['order' => $order, 'payment_intent' => null, 'error' => $e->getMessage()], 201);
        }

        return response()->json(['order' => $order, 'payment_intent' => $intent], 201);
    }

    public function verifyPayment(Request $request)
    {
        $data = $request->validate([
            'order_id' => ['required', 'exists:orders,id'],
            'gateway' => ['required', 'in:razorpay,stripe'],
            'payload' => ['required', 'array'],
        ]);

        $order = Order::findOrFail($data['order_id']);

        try {
            $gateway = $this->gateways->make($data['gateway']);
        } catch (RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        // The gateway itself confirms whether this payment is genuine — the client
        // only tells us WHICH payment to check, never whether it succeeded.
        $result = $gateway->verifyPayment($order, $data['payload']);

        $payment = Payment::create([
            'order_id' => $order->id,
            'gateway' => $data['gateway'],
            'gateway_payment_id' => $result['gateway_payment_id'],
            'amount' => $order->total,
            'status' => $result['verified'] ? 'success' : 'failed',
            'raw_response' => $result['raw'],
        ]);

        if ($result['verified']) {
            $order->update(['payment_status' => 'paid', 'status' => 'confirmed']);
            $order->statusHistory()->create(['status' => 'confirmed', 'note' => 'Payment verified']);
        } else {
            $order->update(['payment_status' => 'failed']);
        }

        return response()->json(['order' => $order->fresh(), 'payment' => $payment]);
    }

    /**
     * Customer has scanned the admin's UPI QR, paid externally, and now submits proof
     * (screenshot + UTR) for manual verification. This does NOT mark the order paid —
     * only an admin reviewing the screenshot in Filament can do that.
     */
    public function submitQrPayment(Request $request)
    {
        $data = $request->validate([
            'order_id' => ['required', 'exists:orders,id'],
            'screenshot' => ['required', 'image', 'max:5120'],
            'utr_reference' => ['required', 'string', 'max:100'],
        ]);

        $order = Order::findOrFail($data['order_id']);

        if ($order->payment_method !== 'qr_manual') {
            return response()->json(['message' => 'This order was not placed with UPI QR payment.'], 422);
        }

        $path = $request->file('screenshot')->store('payment-screenshots', 'public');

        $payment = Payment::updateOrCreate(
            ['order_id' => $order->id, 'gateway' => 'qr_manual'],
            [
                'amount' => $order->total,
                'status' => 'pending_verification',
                'screenshot_path' => $path,
                'utr_reference' => $data['utr_reference'],
                'verified_at' => null,
                'verified_by' => null,
                'rejection_reason' => null,
            ]
        );

        $order->update(['payment_status' => 'pending_verification']);
        $order->statusHistory()->create([
            'status' => $order->status,
            'note' => "Payment proof submitted (UTR: {$data['utr_reference']}) — awaiting manual verification",
        ]);

        return response()->json(['order' => $order->fresh(), 'payment' => $payment], 201);
    }
}
