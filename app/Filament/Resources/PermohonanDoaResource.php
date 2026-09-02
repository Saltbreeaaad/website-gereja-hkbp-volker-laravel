<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PermohonanDoaResource\Pages;
use App\Models\PermohonanDoa;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PermohonanDoaResource extends Resource
{
    protected static ?string $model = PermohonanDoa::class;

    protected static ?string $navigationIcon = 'heroicon-o-heart';

    protected static ?string $navigationGroup = 'Komunikasi';

    protected static ?string $navigationLabel = 'Permohonan Doa';

    protected static ?string $modelLabel = 'Permohonan Doa';

    protected static ?string $pluralModelLabel = 'Permohonan Doa';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Placeholder::make('pengirim')->content(fn (?PermohonanDoa $record): string => $record?->nama ?: 'Dikirim tanpa nama'),
            Forms\Components\Placeholder::make('kontak')->content(fn (?PermohonanDoa $record): string => $record?->kontak ?: 'Tidak ada kontak'),
            Forms\Components\Textarea::make('isi')->label('Pokok doa')->rows(8)->disabled()->columnSpanFull(),
            Forms\Components\Select::make('status')->options(PermohonanDoa::STATUS)->required()->live(),
            Forms\Components\DateTimePicker::make('ditindaklanjuti_pada')->label('Ditindaklanjuti pada')->native(false)->seconds(false)->visible(fn (Forms\Get $get): bool => $get('status') === PermohonanDoa::DITINDAKLANJUTI),
            Forms\Components\Textarea::make('catatan_pengurus')->label('Catatan internal')->rows(4)->maxLength(1200)->columnSpanFull()->helperText('Tidak pernah terlihat oleh pemohon atau publik.'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('created_at')->label('Dikirim')->dateTime('d M Y H:i')->sortable(),
            Tables\Columns\TextColumn::make('nama')->placeholder('Tanpa nama')->searchable(),
            Tables\Columns\TextColumn::make('isi')->label('Pokok doa')->limit(90)->wrap()->searchable(),
            Tables\Columns\TextColumn::make('status')->badge()->formatStateUsing(fn (string $state): string => PermohonanDoa::STATUS[$state] ?? $state),
        ])->defaultSort('created_at', 'desc')->filters([
            Tables\Filters\SelectFilter::make('status')->options(PermohonanDoa::STATUS),
        ])->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ListPermohonanDoa::route('/'), 'edit' => Pages\EditPermohonanDoa::route('/{record}/edit')];
    }
}
