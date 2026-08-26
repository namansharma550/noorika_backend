<?php

namespace App\Services;

use App\Models\Order;
use Barryvdh\DomPDF\Facade\Pdf;
use Barryvdh\DomPDF\PDF as PdfDocument;

class InvoiceService
{
    public function generate(Order $order): PdfDocument
    {
        $order->loadMissing(['items', 'user', 'billingAddress']);

        return Pdf::loadView('invoices.order', ['order' => $order]);
    }
}
