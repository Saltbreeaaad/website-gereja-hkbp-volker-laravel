<?php

namespace App\Filament\Resources;

use App\Filament\Resources\JadwalIbadahResource\Pages;
use App\Models\JadwalIbadah;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class JadwalIbadahResource extends Resource
{
    protected static ?string $model = JadwalIbadah::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar';

    protected static ?string $navigationGroup = 'Ibadah & Renungan';

    protected static ?string $navigationLabel = 'Jadwal Ibadah';

    protected static ?string $modelLabel = 'Jadwal Ibadah';

    protected static ?string $pluralModelLabel = 'Jadwal Ibadah';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nama_ibadah')
                    ->label('Nama Ibadah')
                    ->required()
                    ->maxLength(255)
                    ->placeholder('Cth: Ibadah Minggu - Bahasa Batak')
                    ->columnSpanFull(),

                Forms\Components\DatePicker::make('tanggal')
                    ->required()
                    ->native(false)
                    ->default(now()),

                Forms\Components\TimePicker::make('waktu')
                    ->required()
                    ->seconds(false)
                    ->datalist(['07:00', '09:00', '10:00', '18:00']),

                Forms\Components\TextInput::make('pelayan_firman')
                    ->label('Pelayan Firman')
                    ->maxLength(255)
                    ->placeholder('Cth: Pdt. Paul Benedict, S.Th.'),

                Forms\Components\TextInput::make('keterangan')
                    ->maxLength(255)
                    ->placeholder('Opsional'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('tanggal')
                    ->date('d F Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('waktu')
                    ->label('Jam')
                    ->formatStateUsing(fn ($state) => $state?->format('H:i').' WIB'),

                Tables\Columns\TextColumn::make('nama_ibadah')
                    ->label('Nama Ibadah')
                    ->weight('bold')
                    ->searchable(),

                Tables\Columns\TextColumn::make('pelayan_firman')
                    ->label('Pelayan Firman')
                    ->placeholder('—')
                    ->searchable(),

                Tables\Columns\TextColumn::make('keterangan')
                    ->placeholder('—')
                    ->toggleable(),
            ])
            ->defaultSort('tanggal', 'desc')
            ->filters([
                Tables\Filters\Filter::make('mendatang')
                    ->label('Hanya jadwal mendatang')
                    ->query(fn (Builder $query) => $query->whereDate('tanggal', '>=', today()))
                    ->default(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('Belum ada jadwal ibadah');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListJadwalIbadah::route('/'),
            'create' => Pages\CreateJadwalIbadah::route('/create'),
            'edit' => Pages\EditJadwalIbadah::route('/{record}/edit'),
        ];
    }
}
