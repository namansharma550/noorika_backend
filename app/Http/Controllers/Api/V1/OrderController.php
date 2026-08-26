<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\InvoiceService;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function __construct(protected InvoiceService $invoices) {}

    public function index(Request $request)
    {
        return $request->user()->orders()->with('items')->latest('placed_at')->paginate(10);
    }

    public function show(Request $request, string $orderNumber)
    {
        return $request->user()->orders()
            ->where('order_number', $orderNumber)
            ->with(['items.product', 'statusHistory', 'shippingAddress', 'billingAddress'])
            ->firstOrFail();
    }

    public function invoice(Request $request, string $orderNumber)
    {
        $order = $request->user()->orders()->where('order_number', $orderNumber)->firstOrFail();

        return $this->invoices->generate($order)->download("invoice-{$order->order_number}.pdf");
    }
}
