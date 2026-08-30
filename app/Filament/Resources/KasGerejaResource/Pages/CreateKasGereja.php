<?php

namespace App\Filament\Resources\KasGerejaResource\Pages;

use App\Filament\Resources\KasGerejaResource;
use Filament\Resources\Pages\CreateRecord;

class CreateKasGereja extends CreateRecord
{
    protected static string $resource = KasGerejaResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
