<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PengumumanPentingResource\Pages;
use App\Models\PengumumanPenting;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PengumumanPentingResource extends Resource
{
    protected static ?string $model = PengumumanPenting::class;

    protected static ?string $navigationIcon = 'heroicon-o-megaphone';

    protected static ?string $navigationGroup = 'Komunikasi';

    protected static ?string $navigationLabel = 'Pengumuman Penting';

    protected static ?string $modelLabel = 'Pengumuman';

    protected static ?string $pluralModelLabel = 'Pengumuman Penting';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('judul')->required()->maxLength(255)->columnSpanFull(),
            Forms\Components\Textarea::make('isi')->required()->rows(4)->maxLength(1200)->columnSpanFull(),
            Forms\Components\TextInput::make('tautan')->url()->maxLength(2048)->helperText('Opsional; gunakan tautan https:// yang tepercaya.'),
            Forms\Components\TextInput::make('label_tautan')->maxLength(80)->visible(fn (Forms\Get $get): bool => filled($get('tautan'))),
            Forms\Components\DateTimePicker::make('mulai_tayang')->native(false)->seconds(false),
            Forms\Components\DateTimePicker::make('selesai_tayang')->native(false)->seconds(false)->after('mulai_tayang'),
            Forms\Components\Toggle::make('aktif')->default(true)->inline(false),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('judul')->weight('bold')->searchable()->wrap(),
            Tables\Columns\IconColumn::make('aktif')->boolean(),
            Tables\Columns\TextColumn::make('mulai_tayang')->label('Mulai')->dateTime('d M Y H:i')->placeholder('Sekarang')->sortable(),
            Tables\Columns\TextColumn::make('selesai_tayang')->label('Selesai')->dateTime('d M Y H:i')->placeholder('Tanpa batas')->sortable(),
        ])->defaultSort('created_at', 'desc')->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ListPengumumanPenting::route('/'), 'create' => Pages\CreatePengumumanPenting::route('/create'), 'edit' => Pages\EditPengumumanPenting::route('/{record}/edit')];
    }
}
