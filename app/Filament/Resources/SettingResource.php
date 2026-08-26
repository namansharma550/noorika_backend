<?php

namespace App\Filament\Resources;

use App\Filament\Concerns\HasPermissionGates;
use App\Filament\Resources\SettingResource\Pages;
use App\Models\Setting;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SettingResource extends Resource
{
    use HasPermissionGates;

    protected static ?string $model = Setting::class;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $navigationGroup = 'Settings';

    /** Keys with a dedicated, purpose-built form. Anything else falls back to a generic key-value editor. */
    protected static array $knownSchemas = ['payment_razorpay', 'payment_stripe'];

    /** Keys managed entirely by their own dedicated Filament page — hidden here to avoid two conflicting edit paths. */
    protected static array $hiddenKeys = ['payment_qr', 'site_branding'];

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('key')
                    ->required()
                    ->maxLength(255)
                    ->disabled(fn (string $operation): bool => $operation === 'edit')
                    ->dehydrated(),

                Forms\Components\Group::make([
                    Forms\Components\Select::make('value.mode')
                        ->label('Mode')
                        ->options(['test' => 'Test', 'live' => 'Live'])
                        ->default('test')
                        ->required(),
                    Forms\Components\TextInput::make('value.key_id')
                        ->label('Key ID'),
                    Forms\Components\TextInput::make('value.key_secret')
                        ->label('Key Secret')
                        ->password()
                        ->revealable(),
                ])
                    ->visible(fn (Forms\Get $get): bool => $get('key') === 'payment_razorpay')
                    ->dehydrated(fn (Forms\Get $get): bool => $get('key') === 'payment_razorpay')
                    ->columnSpanFull(),

                Forms\Components\Group::make([
                    Forms\Components\Select::make('value.mode')
                        ->label('Mode')
                        ->options(['test' => 'Test', 'live' => 'Live'])
                        ->default('test')
                        ->required(),
                    Forms\Components\TextInput::make('value.publishable_key')
                        ->label('Publishable Key'),
                    Forms\Components\TextInput::make('value.secret_key')
                        ->label('Secret Key')
                        ->password()
                        ->revealable(),
                ])
                    ->visible(fn (Forms\Get $get): bool => $get('key') === 'payment_stripe')
                    ->dehydrated(fn (Forms\Get $get): bool => $get('key') === 'payment_stripe')
                    ->columnSpanFull(),

                Forms\Components\KeyValue::make('value')
                    ->visible(fn (Forms\Get $get): bool => ! in_array($get('key'), self::$knownSchemas, true))
                    ->dehydrated(fn (Forms\Get $get): bool => ! in_array($get('key'), self::$knownSchemas, true))
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->whereNotIn('key', self::$hiddenKeys))
            ->columns([
                Tables\Columns\TextColumn::make('key')
                    ->searchable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSettings::route('/'),
            'create' => Pages\CreateSetting::route('/create'),
            'edit' => Pages\EditSetting::route('/{record}/edit'),
        ];
    }
}
