<?php

namespace App\Filament\Resources\PenggunaanGerejaResource\Pages;

use App\Filament\Resources\PenggunaanGerejaResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPenggunaanGerejas extends ListRecords
{
    protected static string $resource = PenggunaanGerejaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
