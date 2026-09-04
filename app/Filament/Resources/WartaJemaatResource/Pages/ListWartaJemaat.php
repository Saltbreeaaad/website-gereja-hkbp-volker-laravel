<?php

namespace App\Filament\Resources\WartaJemaatResource\Pages;

use App\Filament\Resources\WartaJemaatResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListWartaJemaat extends ListRecords
{
    protected static string $resource = WartaJemaatResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
