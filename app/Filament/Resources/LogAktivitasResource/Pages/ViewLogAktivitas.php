<?php

namespace App\Filament\Resources\LogAktivitasResource\Pages;

use App\Filament\Resources\LogAktivitasResource;
use App\Models\LogAktivitas;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;

class ViewLogAktivitas extends ViewRecord
{
    protected static string $resource = LogAktivitasResource::class;

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Infolists\Components\TextEntry::make('created_at')->label('Waktu')->dateTime('d F Y, H:i:s'),
            Infolists\Components\TextEntry::make('user.name')->label('Pengurus')->placeholder('Sistem'),
            Infolists\Components\TextEntry::make('aksi')->badge(),
            Infolists\Components\TextEntry::make('subjek_tipe')->label('Jenis data')->formatStateUsing(fn (string $state): string => class_basename($state)),
            Infolists\Components\TextEntry::make('ringkasan')->columnSpanFull(),
            // `state()`, bukan `formatStateUsing()`.
            //
            // Kolomnya di-cast ke array, dan TextEntry memperlakukan state array
            // sebagai daftar nilai: ia memanggil formatStateUsing sekali untuk
            // TIAP elemen, sehingga closure bertipe `?array` menerima string dan
            // seluruh halaman melempar TypeError. Menyerahkan string yang sudah
            // jadi membuat entri ini punya satu nilai skalar, bukan daftar.
            Infolists\Components\TextEntry::make('perubahan')
                ->label('Perubahan')
                ->state(fn (LogAktivitas $record): string => filled($record->perubahan)
                    ? (json_encode($record->perubahan, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) ?: '—')
                    : '—')
                ->fontFamily('mono')
                ->columnSpanFull(),
            Infolists\Components\TextEntry::make('ip_address')->label('Alamat IP'),
            Infolists\Components\TextEntry::make('user_agent')->label('Perangkat')->columnSpanFull(),
        ])->columns(2);
    }
}
