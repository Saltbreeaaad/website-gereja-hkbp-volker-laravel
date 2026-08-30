<?php

namespace App\Filament\Resources\ParhaladoResource\Pages;

use App\Filament\Resources\ParhaladoResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditParhalado extends EditRecord
{
    protected static string $resource = ParhaladoResource::class;

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
