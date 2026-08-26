<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Payment extends Model
{
    protected $fillable = [
        'order_id', 'gateway', 'gateway_payment_id', 'amount', 'status', 'raw_response',
        'screenshot_path', 'utr_reference', 'verified_at', 'verified_by', 'rejection_reason',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'raw_response' => 'array',
        'verified_at' => 'datetime',
    ];

    protected $appends = ['screenshot_url'];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    protected function screenshotUrl(): \Illuminate\Database\Eloquent\Casts\Attribute
    {
        return \Illuminate\Database\Eloquent\Casts\Attribute::get(
            fn () => $this->screenshot_path ? Storage::disk('public')->url($this->screenshot_path) : null
        );
    }
}
