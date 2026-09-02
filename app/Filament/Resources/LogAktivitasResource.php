<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LogAktivitasResource\Pages;
use App\Models\LogAktivitas;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class LogAktivitasResource extends Resource
{
    protected static ?string $model = LogAktivitas::class;

    protected static ?string $navigationIcon = 'heroicon-o-clock';

    protected static ?string $navigationGroup = 'Administrasi';

    protected static ?string $navigationLabel = 'Riwayat Aktivitas';

    protected static ?string $modelLabel = 'Aktivitas';

    protected static ?string $pluralModelLabel = 'Riwayat Aktivitas';

    protected static ?int $navigationSort = 10;

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Waktu')
                    ->dateTime('d M Y, H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Pengurus')
                    ->placeholder('Sistem')
                    ->searchable(),
                Tables\Columns\TextColumn::make('aksi')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'dibuat' => 'success',
                        'dihapus' => 'danger',
                        default => 'warning',
                    }),
                Tables\Columns\TextColumn::make('subjek_tipe')
                    ->label('Data')
                    ->formatStateUsing(fn (string $state): string => class_basename($state)),
                Tables\Columns\TextColumn::make('ringkasan')
                    ->wrap()
                    ->searchable(),
                Tables\Columns\TextColumn::make('ip_address')
                    ->label('Alamat IP')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('aksi')
                    ->options([
                        'dibuat' => 'Dibuat',
                        'diubah' => 'Diubah',
                        'dihapus' => 'Dihapus',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLogAktivitas::route('/'),
            'view' => Pages\ViewLogAktivitas::route('/{record}'),
        ];
    }
}
