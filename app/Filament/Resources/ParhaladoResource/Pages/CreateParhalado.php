<?php

namespace App\Filament\Resources\ParhaladoResource\Pages;

use App\Filament\Resources\ParhaladoResource;
use Filament\Resources\Pages\CreateRecord;

class CreateParhalado extends CreateRecord
{
    protected static string $resource = ParhaladoResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
