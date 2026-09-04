<?php

namespace App\Filament\Resources\PenggunaanGerejaResource\Pages;

use App\Filament\Resources\PenggunaanGerejaResource;
use App\Models\PenggunaanGereja;
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

    /**
     * Status juga dapat diubah dari formulir ini, bukan hanya lewat tombol
     * Setujui/Tolak di tabel — dan justru di sinilah "Catatan untuk Pemohon"
     * ditulis. Pengingatnya karena itu harus ikut ke jalur ini.
     */
    protected function afterSave(): void
    {
        /** @var PenggunaanGereja $record */
        $record = $this->record;

        if ($record->wasChanged('status')) {
            PenggunaanGerejaResource::ingatkanKabariPemohon($record);
        }
    }
}
