<?php

namespace App\Filament\Resources\PenggunaanGerejaResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class RiwayatStatusRelationManager extends RelationManager
{
    protected static string $relationship = 'riwayatStatus';

    protected static ?string $title = 'Riwayat Status';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('status_baru')
            ->columns([
                Tables\Columns\TextColumn::make('created_at')->label('Waktu')->dateTime('d M Y, H:i'),
                Tables\Columns\TextColumn::make('status_lama')->label('Dari')->placeholder('Permohonan baru'),
                Tables\Columns\TextColumn::make('status_baru')->label('Menjadi')->badge(),
                Tables\Columns\TextColumn::make('user.name')->label('Oleh')->placeholder('Sistem'),
                Tables\Columns\TextColumn::make('catatan')->wrap()->placeholder('—'),
            ])
            ->headerActions([])
            ->actions([])
            ->bulkActions([]);
    }
}
