<?php

namespace App\Filament\Resources\PenggunaanGerejaResource\Pages;

use App\Filament\Resources\PenggunaanGerejaResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPenggunaanGereja extends EditRecord
{
    protected static string $resource = PenggunaanGerejaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
