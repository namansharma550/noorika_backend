<?php

namespace App\Filament\Resources\ContactEnquiryResource\Pages;

use App\Filament\Resources\ContactEnquiryResource;
use App\Models\ContactEnquiry;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;

class ViewContactEnquiry extends ViewRecord
{
    protected static string $resource = ContactEnquiryResource::class;

    public function mount($record): void
    {
        parent::mount($record);

        /** @var ContactEnquiry $enquiry */
        $enquiry = $this->record;

        if ($enquiry->status === 'new') {
            $enquiry->update(['status' => 'read']);
        }
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                TextEntry::make('name'),
                TextEntry::make('email'),
                TextEntry::make('phone')->default('—'),
                TextEntry::make('subject')->default('—'),
                TextEntry::make('message')->columnSpanFull(),
                TextEntry::make('status')->badge(),
                TextEntry::make('created_at')->dateTime()->label('Submitted'),
            ]);
    }
}
