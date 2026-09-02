<?php

namespace App\Filament\Resources\PengumumanPentingResource\Pages;

use App\Filament\Resources\PengumumanPentingResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPengumumanPenting extends ListRecords
{
    protected static string $resource = PengumumanPentingResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
