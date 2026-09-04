<?php

namespace App\Filament\Resources\RenunganResource\Pages;

use App\Filament\Resources\RenunganResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListRenungan extends ListRecords
{
    protected static string $resource = RenunganResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
