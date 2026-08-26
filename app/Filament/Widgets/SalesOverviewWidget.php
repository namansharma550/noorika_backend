<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use App\Models\Product;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class SalesOverviewWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $paidToday = Order::whereDate('placed_at', Carbon::today())
            ->where('payment_status', 'paid')
            ->sum('total');

        $paidThisWeek = Order::whereBetween('placed_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])
            ->where('payment_status', 'paid')
            ->sum('total');

        $paidThisMonth = Order::whereMonth('placed_at', Carbon::now()->month)
            ->whereYear('placed_at', Carbon::now()->year)
            ->where('payment_status', 'paid')
            ->sum('total');

        $pendingOrders = Order::where('status', 'pending')->count();

        $lowStockCount = Product::where('status', 'active')->where('stock_qty', '<=', 5)->count();

        return [
            Stat::make('Sales Today', '₹'.number_format($paidToday, 2)),
            Stat::make('Sales This Week', '₹'.number_format($paidThisWeek, 2)),
            Stat::make('Sales This Month', '₹'.number_format($paidThisMonth, 2)),
            Stat::make('Pending Orders', $pendingOrders)
                ->color($pendingOrders > 0 ? 'warning' : 'success'),
            Stat::make('Low Stock Products', $lowStockCount)
                ->description('5 or fewer in stock')
                ->color($lowStockCount > 0 ? 'danger' : 'success'),
        ];
    }
}
