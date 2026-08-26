<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Invoice {{ $order->order_number }}</title>
<style>
    body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #241012; }
    .header { display: flex; justify-content: space-between; margin-bottom: 24px; }
    .brand { font-size: 22px; font-weight: bold; color: #550B1E; }
    .meta { text-align: right; }
    table { width: 100%; border-collapse: collapse; margin-top: 16px; }
    th, td { text-align: left; padding: 8px; border-bottom: 1px solid #ddd; }
    th { background: #EDE3E3; }
    .totals { width: 260px; margin-left: auto; margin-top: 16px; }
    .totals td { border: none; padding: 4px 8px; }
    .totals .grand { font-weight: bold; font-size: 14px; border-top: 2px solid #550B1E; }
    .address { margin-top: 24px; }
</style>
</head>
<body>
    <div class="header">
        <div class="brand">Noorika</div>
        <div class="meta">
            <div><strong>Invoice:</strong> {{ $order->order_number }}</div>
            <div><strong>Date:</strong> {{ $order->placed_at?->format('d M Y') }}</div>
            <div><strong>Payment:</strong> {{ ucfirst($order->payment_method) }} ({{ ucfirst($order->payment_status) }})</div>
        </div>
    </div>

    <div class="address">
        <strong>Billed To:</strong><br>
        {{ $order->user?->name ?? 'Guest' }}<br>
        @if($order->billingAddress)
            {{ $order->billingAddress->line1 }}@if($order->billingAddress->line2), {{ $order->billingAddress->line2 }}@endif<br>
            {{ $order->billingAddress->city }}, {{ $order->billingAddress->state }} {{ $order->billingAddress->pincode }}<br>
            {{ $order->billingAddress->country }}
        @endif
    </div>

    <table>
        <thead>
            <tr>
                <th>Item</th>
                <th>Price</th>
                <th>Qty</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($order->items as $item)
            <tr>
                <td>{{ $item->product_name_snapshot }}</td>
                <td>₹{{ number_format($item->price_snapshot, 2) }}</td>
                <td>{{ $item->quantity }}</td>
                <td>₹{{ number_format($item->subtotal, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <table class="totals">
        <tr><td>Subtotal</td><td>₹{{ number_format($order->subtotal, 2) }}</td></tr>
        <tr><td>Discount</td><td>-₹{{ number_format($order->discount, 2) }}</td></tr>
        <tr><td>Shipping</td><td>₹{{ number_format($order->shipping_fee, 2) }}</td></tr>
        <tr><td>Tax</td><td>₹{{ number_format($order->tax, 2) }}</td></tr>
        <tr class="grand"><td>Total</td><td>₹{{ number_format($order->total, 2) }}</td></tr>
    </table>
</body>
</html>
