<?php

namespace App\Filament\Resources\KasGerejaResource\Pages;

use App\Filament\Resources\KasGerejaResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditKasGereja extends EditRecord
{
    protected static string $resource = KasGerejaResource::class;

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
