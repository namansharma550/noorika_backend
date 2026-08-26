<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Support\Facades\Storage;

class SettingController extends Controller
{
    /**
     * Publicly readable, non-sensitive site settings for the storefront —
     * deliberately NOT a passthrough of the whole settings table, since keys
     * like payment_razorpay/payment_stripe hold secret credentials.
     */
    public function branding()
    {
        $branding = Setting::find('site_branding')?->value ?? [];

        $logoUrl = isset($branding['logo']) ? Storage::disk('public')->url($branding['logo']) : null;
        $footerLogoUrl = isset($branding['footer_logo']) ? Storage::disk('public')->url($branding['footer_logo']) : null;

        return response()->json([
            'logo_url' => $logoUrl,
            // No dedicated footer logo uploaded — reuse the header logo rather than showing nothing.
            'footer_logo_url' => $footerLogoUrl ?? $logoUrl,
            'favicon_url' => isset($branding['favicon']) ? Storage::disk('public')->url($branding['favicon']) : null,
        ]);
    }

    /** Public UPI/QR payment details for the checkout QR popup — only exposed once an admin has uploaded a QR image. */
    public function qrPayment()
    {
        $qr = Setting::find('payment_qr')?->value ?? [];

        if (empty($qr['qr_image'])) {
            return response()->json(['available' => false]);
        }

        return response()->json([
            'available' => true,
            'qr_image_url' => Storage::disk('public')->url($qr['qr_image']),
            'upi_id' => $qr['upi_id'] ?? null,
            'payee_name' => $qr['payee_name'] ?? null,
        ]);
    }
}
