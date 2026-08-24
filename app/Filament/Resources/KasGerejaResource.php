<?php

namespace App\Filament\Resources;

use App\Filament\Resources\KasGerejaResource\Pages;
use App\Models\KasGereja;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Support\RawJs;

class KasGerejaResource extends Resource
{
    protected static ?string $model = KasGereja::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $navigationLabel = 'Kas Gereja';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\DatePicker::make('tanggal')
                    ->required()
                    ->default(now()),
                
                Forms\Components\Select::make('jenis')
                    ->options([
                        'Pemasukan' => 'Pemasukan',
                        'Pengeluaran' => 'Pengeluaran',
                    ])
                    ->required()
                    ->native(false),
                
                Forms\Components\TextInput::make('keterangan')
                    ->required()
                    ->maxLength(255)
                    ->placeholder('Cth: Persembahan Kantong I'),
                
                Forms\Components\TextInput::make('nominal')
                    ->required()
                    ->prefix('Rp')
                    ->mask(RawJs::make('$money($input, \',\', \'.\', 0)'))
                    ->stripCharacters('.')
                    ->numeric(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('tanggal')
                    ->date('d F Y')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('jenis')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Pemasukan' => 'success',
                        'Pengeluaran' => 'danger',
                    })
                    ->searchable(),
                
                Tables\Columns\TextColumn::make('keterangan')
                    ->searchable(),
                
                Tables\Columns\TextColumn::make('nominal')
                    ->money('IDR', locale: 'id')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListKasGerejas::route('/'),
            'create' => Pages\CreateKasGereja::route('/create'),
            'edit' => Pages\EditKasGereja::route('/{record}/edit'),
        ];
    }
}