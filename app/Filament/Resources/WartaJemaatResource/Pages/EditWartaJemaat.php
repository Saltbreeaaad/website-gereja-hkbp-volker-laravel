<?php

namespace App\Filament\Resources\WartaJemaatResource\Pages;

use App\Filament\Resources\WartaJemaatResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditWartaJemaat extends EditRecord
{
    protected static string $resource = WartaJemaatResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
    
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}