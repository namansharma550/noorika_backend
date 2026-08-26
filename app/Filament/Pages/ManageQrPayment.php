<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class ManageQrPayment extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-qr-code';

    protected static ?string $navigationGroup = 'Settings';

    protected static ?string $navigationLabel = 'UPI QR Payment';

    protected static ?string $title = 'UPI QR Payment';

    protected static string $view = 'filament.pages.manage-qr-payment';

    public ?array $data = [];

    public function mount(): void
    {
        abort_unless(auth()->user()?->can('update_setting'), 403);

        $setting = Setting::firstOrCreate(
            ['key' => 'payment_qr'],
            ['value' => ['qr_image' => '', 'upi_id' => '', 'payee_name' => '']]
        );

        $this->form->fill($setting->value);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\FileUpload::make('qr_image')
                    ->label('UPI QR Code Image')
                    ->image()
                    ->directory('site')
                    ->helperText('Shown to customers when they choose "Pay via UPI QR" at checkout. Leave empty to hide this payment option.'),
                Forms\Components\TextInput::make('upi_id')
                    ->label('UPI ID')
                    ->helperText('e.g. noorika@okhdfcbank — shown alongside the QR as a fallback.'),
                Forms\Components\TextInput::make('payee_name')
                    ->label('Payee Name')
                    ->helperText('Name customers will see the payment is going to.'),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        abort_unless(auth()->user()?->can('update_setting'), 403);

        $data = $this->form->getState();

        Setting::updateOrCreate(['key' => 'payment_qr'], ['value' => $data]);

        Notification::make()
            ->title('UPI QR payment settings saved')
            ->success()
            ->send();
    }
}
