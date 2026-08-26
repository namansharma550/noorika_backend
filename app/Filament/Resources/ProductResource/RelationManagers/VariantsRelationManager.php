<?php

namespace App\Filament\Resources\ProductResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class VariantsRelationManager extends RelationManager
{
    protected static string $relationship = 'variants';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('variant_name')
                    ->required()
                    ->maxLength(255)
                    ->placeholder('e.g. Size'),
                Forms\Components\TextInput::make('variant_value')
                    ->required()
                    ->maxLength(255)
                    ->placeholder('e.g. 2.4'),
                Forms\Components\TextInput::make('price_delta')
                    ->numeric()
                    ->prefix('₹')
                    ->default(0),
                Forms\Components\TextInput::make('stock_qty')
                    ->numeric()
                    ->default(0),
                Forms\Components\TextInput::make('sku_suffix')
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('variant_name')
            ->columns([
                Tables\Columns\TextColumn::make('variant_name'),
                Tables\Columns\TextColumn::make('variant_value'),
                Tables\Columns\TextColumn::make('price_delta')->money('INR'),
                Tables\Columns\TextColumn::make('stock_qty'),
                Tables\Columns\TextColumn::make('sku_suffix'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
