<?php

namespace App\Filament\Resources;

use App\Filament\Concerns\HasPermissionGates;
use App\Filament\Resources\PaymentResource\Pages;
use App\Filament\Resources\PaymentResource\RelationManagers;
use App\Models\Payment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class PaymentResource extends Resource
{
    use HasPermissionGates;

    protected static ?string $model = Payment::class;

    protected static ?string $navigationIcon = 'heroicon-o-credit-card';

    protected static ?string $navigationGroup = 'Sales';

    // Payments are written by the gateway webhook flow (CheckoutController::verifyPayment)
    // or by the customer submitting UPI proof — not created by hand — this resource is read-only on create.
    public static function canCreate(): bool
    {
        return false;
    }

    public static function getNavigationBadge(): ?string
    {
        $count = static::getModel()::where('status', 'pending_verification')->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('order_id')
                    ->relationship('order', 'order_number')
                    ->disabled(),
                Forms\Components\TextInput::make('gateway')
                    ->disabled(),
                Forms\Components\TextInput::make('gateway_payment_id')
                    ->disabled(),
                Forms\Components\TextInput::make('utr_reference')
                    ->label('UTR / UPI Reference')
                    ->disabled(),
                Forms\Components\TextInput::make('amount')
                    ->numeric()
                    ->prefix('₹')
                    ->disabled(),
                Forms\Components\Select::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'pending_verification' => 'Pending Verification',
                        'success' => 'Success',
                        'failed' => 'Failed',
                        'refunded' => 'Refunded',
                    ])
                    ->required(),
                Forms\Components\Textarea::make('rejection_reason')
                    ->columnSpanFull(),
                Forms\Components\ViewField::make('screenshot_url')
                    ->label('Payment Screenshot')
                    ->view('filament.forms.components.payment-screenshot')
                    ->columnSpanFull()
                    ->visible(fn (?Payment $record) => $record?->screenshot_url),
                Forms\Components\Textarea::make('raw_response')
                    ->columnSpanFull()
                    ->disabled(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('screenshot_url')
                    ->label('Proof')
                    ->square()
                    ->size(48),
                Tables\Columns\TextColumn::make('order.order_number')
                    ->label('Order')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('gateway')
                    ->searchable(),
                Tables\Columns\TextColumn::make('utr_reference')
                    ->label('UTR')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('amount')
                    ->money('INR')
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'warning' => ['pending', 'pending_verification'],
                        'success' => 'success',
                        'danger' => 'failed',
                        'gray' => 'refunded',
                    ]),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('verifier.name')
                    ->label('Verified By')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'pending_verification' => 'Pending Verification',
                        'success' => 'Success',
                        'failed' => 'Failed',
                        'refunded' => 'Refunded',
                    ]),
                Tables\Filters\SelectFilter::make('gateway')
                    ->options([
                        'razorpay' => 'Razorpay',
                        'stripe' => 'Stripe',
                        'qr_manual' => 'UPI QR (Manual)',
                    ]),
            ])
            ->actions([
                Tables\Actions\Action::make('verify')
                    ->label('Verify')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (Payment $record) => $record->status === 'pending_verification')
                    ->requiresConfirmation()
                    ->action(function (Payment $record) {
                        $record->update([
                            'status' => 'success',
                            'verified_at' => now(),
                            'verified_by' => Auth::id(),
                            'rejection_reason' => null,
                        ]);
                        $record->order->update(['payment_status' => 'paid', 'status' => 'confirmed']);
                        $record->order->statusHistory()->create([
                            'status' => 'confirmed',
                            'note' => 'UPI payment verified by admin',
                            'changed_by' => Auth::id(),
                        ]);
                        Notification::make()->title('Payment verified')->success()->send();
                    }),
                Tables\Actions\Action::make('reject')
                    ->label('Reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (Payment $record) => $record->status === 'pending_verification')
                    ->form([
                        Forms\Components\Textarea::make('rejection_reason')
                            ->label('Reason')
                            ->required(),
                    ])
                    ->action(function (Payment $record, array $data) {
                        $record->update([
                            'status' => 'failed',
                            'verified_at' => now(),
                            'verified_by' => Auth::id(),
                            'rejection_reason' => $data['rejection_reason'],
                        ]);
                        $record->order->update(['payment_status' => 'failed']);
                        $record->order->statusHistory()->create([
                            'status' => $record->order->status,
                            'note' => 'UPI payment rejected: '.$data['rejection_reason'],
                            'changed_by' => Auth::id(),
                        ]);
                        Notification::make()->title('Payment rejected')->warning()->send();
                    }),
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
            'index' => Pages\ListPayments::route('/'),
            'edit' => Pages\EditPayment::route('/{record}/edit'),
        ];
    }
}
