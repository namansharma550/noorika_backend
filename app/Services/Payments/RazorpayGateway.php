<?php

namespace App\Services\Payments;

use App\Models\Order;
use Razorpay\Api\Api;
use Razorpay\Api\Errors\SignatureVerificationError;

class RazorpayGateway implements PaymentGateway
{
    public function __construct(
        protected string $keyId,
        protected string $keySecret,
    ) {}

    protected function client(): Api
    {
        return new Api($this->keyId, $this->keySecret);
    }

    public function createIntent(Order $order): array
    {
        $razorpayOrder = $this->client()->order->create([
            // Razorpay expects the smallest currency unit (paise).
            'amount' => (int) round($order->total * 100),
            'currency' => 'INR',
            'receipt' => $order->order_number,
        ]);

        return [
            'gateway' => 'razorpay',
            'key_id' => $this->keyId,
            'gateway_order_id' => $razorpayOrder['id'],
            'amount' => $razorpayOrder['amount'],
            'currency' => $razorpayOrder['currency'],
        ];
    }

    public function verifyPayment(Order $order, array $payload): array
    {
        $attributes = [
            'razorpay_order_id' => $payload['razorpay_order_id'] ?? null,
            'razorpay_payment_id' => $payload['razorpay_payment_id'] ?? null,
            'razorpay_signature' => $payload['razorpay_signature'] ?? null,
        ];

        try {
            $this->client()->utility->verifyPaymentSignature($attributes);
            $verified = true;
        } catch (SignatureVerificationError) {
            $verified = false;
        }

        return [
            'verified' => $verified,
            'gateway_payment_id' => $attributes['razorpay_payment_id'],
            'raw' => $attributes,
        ];
    }
}
