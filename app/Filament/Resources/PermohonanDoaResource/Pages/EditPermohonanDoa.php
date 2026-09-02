<?php

namespace App\Filament\Resources\PermohonanDoaResource\Pages;

use App\Filament\Resources\PermohonanDoaResource;
use App\Models\PermohonanDoa;
use Filament\Resources\Pages\EditRecord;

class EditPermohonanDoa extends EditRecord
{
    protected static string $resource = PermohonanDoaResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (($data['status'] ?? null) === PermohonanDoa::DITINDAKLANJUTI && blank($data['ditindaklanjuti_pada'])) {
            $data['ditindaklanjuti_pada'] = now();
        }

        return $data;
    }
}
