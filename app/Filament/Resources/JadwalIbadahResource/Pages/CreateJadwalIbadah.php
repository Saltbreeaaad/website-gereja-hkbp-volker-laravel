<?php

namespace App\Filament\Resources\JadwalIbadahResource\Pages;

use App\Filament\Resources\JadwalIbadahResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateJadwalIbadah extends CreateRecord
{
    protected static string $resource = JadwalIbadahResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}