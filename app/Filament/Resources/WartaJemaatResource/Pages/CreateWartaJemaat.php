<?php

namespace App\Filament\Resources\WartaJemaatResource\Pages;

use App\Filament\Resources\WartaJemaatResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateWartaJemaat extends CreateRecord
{
    protected static string $resource = WartaJemaatResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}