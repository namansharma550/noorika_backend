<?php

namespace App\Services\Payments;

use App\Models\Order;
use Stripe\Exception\ApiErrorException;
use Stripe\StripeClient;

class StripeGateway implements PaymentGateway
{
    public function __construct(
        protected string $secretKey,
        protected string $publishableKey,
    ) {}

    protected function client(): StripeClient
    {
        return new StripeClient($this->secretKey);
    }

    public function createIntent(Order $order): array
    {
        $intent = $this->client()->paymentIntents->create([
            // Stripe also expects the smallest currency unit (paise for INR).
            'amount' => (int) round($order->total * 100),
            'currency' => 'inr',
            'metadata' => ['order_number' => $order->order_number],
        ]);

        return [
            'gateway' => 'stripe',
            'publishable_key' => $this->publishableKey,
            'client_secret' => $intent->client_secret,
            'payment_intent_id' => $intent->id,
            'amount' => $intent->amount,
            'currency' => $intent->currency,
        ];
    }

    public function verifyPayment(Order $order, array $payload): array
    {
        $paymentIntentId = $payload['payment_intent_id'] ?? null;

        if (! $paymentIntentId) {
            return ['verified' => false, 'gateway_payment_id' => null, 'raw' => []];
        }

        try {
            // Re-fetch from Stripe's servers rather than trusting the client's
            // reported status — the source of truth is the gateway, not the browser.
            $intent = $this->client()->paymentIntents->retrieve($paymentIntentId);
        } catch (ApiErrorException) {
            return ['verified' => false, 'gateway_payment_id' => $paymentIntentId, 'raw' => []];
        }

        return [
            'verified' => $intent->status === 'succeeded',
            'gateway_payment_id' => $intent->id,
            'raw' => $intent->toArray(),
        ];
    }
}
