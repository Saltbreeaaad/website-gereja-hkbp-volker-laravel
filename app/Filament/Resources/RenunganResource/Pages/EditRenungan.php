<?php

namespace App\Filament\Resources\RenunganResource\Pages;

use App\Filament\Resources\RenunganResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditRenungan extends EditRecord
{
    protected static string $resource = RenunganResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
