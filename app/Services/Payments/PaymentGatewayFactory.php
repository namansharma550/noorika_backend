<?php

namespace App\Services\Payments;

use App\Models\Setting;
use RuntimeException;

class PaymentGatewayFactory
{
    /**
     * @throws RuntimeException when the requested gateway has no configured keys.
     */
    public function make(string $gateway): PaymentGateway
    {
        return match ($gateway) {
            'razorpay' => $this->makeRazorpay(),
            'stripe' => $this->makeStripe(),
            default => throw new RuntimeException("Unsupported payment gateway: {$gateway}"),
        };
    }

    /** Gateways an admin has actually configured keys for — drives the checkout page's payment method options. */
    public function availableGateways(): array
    {
        $available = [];

        $razorpay = $this->settingValue('payment_razorpay');
        if (! empty($razorpay['key_id']) && ! empty($razorpay['key_secret'])) {
            $available[] = 'razorpay';
        }

        $stripe = $this->settingValue('payment_stripe');
        if (! empty($stripe['secret_key']) && ! empty($stripe['publishable_key'])) {
            $available[] = 'stripe';
        }

        $qr = $this->settingValue('payment_qr');
        if (! empty($qr['qr_image'])) {
            $available[] = 'qr_manual';
        }

        return $available;
    }

    protected function makeRazorpay(): RazorpayGateway
    {
        $config = $this->settingValue('payment_razorpay');

        if (empty($config['key_id']) || empty($config['key_secret'])) {
            throw new RuntimeException('Razorpay is not configured. Add keys in Settings.');
        }

        return new RazorpayGateway($config['key_id'], $config['key_secret']);
    }

    protected function makeStripe(): StripeGateway
    {
        $config = $this->settingValue('payment_stripe');

        if (empty($config['secret_key']) || empty($config['publishable_key'])) {
            throw new RuntimeException('Stripe is not configured. Add keys in Settings.');
        }

        return new StripeGateway($config['secret_key'], $config['publishable_key']);
    }

    protected function settingValue(string $key): array
    {
        return Setting::find($key)?->value ?? [];
    }
}
