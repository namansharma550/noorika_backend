<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class ManageBranding extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-photo';

    protected static ?string $navigationGroup = 'Settings';

    protected static ?string $navigationLabel = 'Site Branding';

    protected static ?string $title = 'Site Branding';

    protected static string $view = 'filament.pages.manage-branding';

    public ?array $data = [];

    public function mount(): void
    {
        abort_unless(auth()->user()?->can('update_setting'), 403);

        $setting = Setting::firstOrCreate(
            ['key' => 'site_branding'],
            ['value' => ['logo' => '', 'footer_logo' => '', 'favicon' => '']]
        );

        $this->form->fill($setting->value);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\FileUpload::make('logo')
                    ->label('Header Logo')
                    ->image()
                    ->directory('site')
                    ->helperText('Shown in the storefront header. Transparent PNG recommended.'),
                Forms\Components\FileUpload::make('footer_logo')
                    ->label('Footer Logo')
                    ->image()
                    ->directory('site')
                    ->helperText('Shown in the storefront footer. Falls back to the header logo if left empty.'),
                Forms\Components\FileUpload::make('favicon')
                    ->label('Favicon')
                    ->image()
                    ->directory('site')
                    ->helperText('Browser tab icon. Square PNG or ICO, at least 32×32.'),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        abort_unless(auth()->user()?->can('update_setting'), 403);

        $data = $this->form->getState();

        Setting::updateOrCreate(['key' => 'site_branding'], ['value' => $data]);

        Notification::make()
            ->title('Site branding saved')
            ->success()
            ->send();
    }
}
