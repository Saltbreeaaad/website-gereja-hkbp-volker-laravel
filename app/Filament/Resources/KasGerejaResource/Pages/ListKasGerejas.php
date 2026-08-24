<?php

namespace App\Filament\Resources\KasGerejaResource\Pages;

use App\Filament\Resources\KasGerejaResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListKasGerejas extends ListRecords
{
    protected static string $resource = KasGerejaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
