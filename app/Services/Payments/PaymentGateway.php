<?php

namespace App\Services\Payments;

use App\Models\Order;

interface PaymentGateway
{
    /**
     * Create a gateway-side payment/order intent for the given order.
     * Returns data the frontend needs to open the gateway's checkout widget.
     */
    public function createIntent(Order $order): array;

    /**
     * Verify a client-reported payment against the gateway's servers
     * (signature check, or a fetch-by-id call) — never trust the client alone.
     *
     * @param  array  $payload  Gateway-specific fields sent back from the checkout widget.
     * @return array{verified: bool, gateway_payment_id: ?string, raw: array}
     */
    public function verifyPayment(Order $order, array $payload): array;
}
