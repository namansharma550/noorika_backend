<?php

namespace App\Filament\Resources\OrderResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class ItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('product_name_snapshot')
            ->columns([
                Tables\Columns\TextColumn::make('product_name_snapshot')->label('Product'),
                Tables\Columns\TextColumn::make('price_snapshot')->money('INR'),
                Tables\Columns\TextColumn::make('quantity'),
                Tables\Columns\TextColumn::make('subtotal')->money('INR'),
            ])
            ->headerActions([])
            ->actions([])
            ->bulkActions([]);
    }
}
