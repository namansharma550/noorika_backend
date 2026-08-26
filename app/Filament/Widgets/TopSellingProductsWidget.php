<?php

namespace App\Filament\Widgets;

use App\Models\Product;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class TopSellingProductsWidget extends BaseWidget
{
    protected static ?int $sort = 4;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Product::query()
                    ->withSum(['orderItems as units_sold' => function (Builder $query) {
                        $query->whereHas('order', fn ($q) => $q->where('payment_status', 'paid'));
                    }], 'quantity')
                    ->orderByDesc('units_sold')
                    ->limit(10)
            )
            ->heading('Top Selling Products')
            ->columns([
                Tables\Columns\TextColumn::make('name'),
                Tables\Columns\TextColumn::make('category.name'),
                Tables\Columns\TextColumn::make('units_sold')->label('Units Sold')->default(0),
                Tables\Columns\TextColumn::make('price')->money('INR'),
            ])
            ->paginated(false);
    }
}
