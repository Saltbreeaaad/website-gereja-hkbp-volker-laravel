<?php

namespace App\Filament\Resources\PeriodeKasResource\Pages;

use App\Filament\Resources\PeriodeKasResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPeriodeKas extends ListRecords
{
    protected static string $resource = PeriodeKasResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
