<?php

namespace App\Filament\Resources\ParhaladoResource\Pages;

use App\Filament\Resources\ParhaladoResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListParhalado extends ListRecords
{
    protected static string $resource = ParhaladoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
