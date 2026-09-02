<?php

namespace App\Filament\Resources\KasGerejaResource\Pages;

use App\Filament\Resources\KasGerejaResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListKasGerejas extends ListRecords
{
    protected static string $resource = KasGerejaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            Actions\Action::make('laporan')
                ->label('Laporan Cetak/PDF')
                ->icon('heroicon-o-printer')
                ->url(route('admin.kas.laporan'), shouldOpenInNewTab: true),
            Actions\Action::make('csv')
                ->label('Ekspor CSV')
                ->icon('heroicon-o-arrow-down-tray')
                ->url(route('admin.kas.csv')),
        ];
    }
}
