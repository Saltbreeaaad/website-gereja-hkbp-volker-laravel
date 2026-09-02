<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ParhaladoResource\Pages;
use App\Models\Parhalado;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ParhaladoResource extends Resource
{
    protected static ?string $model = Parhalado::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationGroup = 'Jemaat';

    protected static ?string $navigationLabel = 'Pelayan & Pengurus';

    protected static ?string $modelLabel = 'Pelayan';

    protected static ?string $pluralModelLabel = 'Pelayan & Pengurus';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nama')
                    ->required()
                    ->maxLength(255),

                Forms\Components\FileUpload::make('foto')
                    ->image()
                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                    // Dimensi dibatasi, bukan hanya ukuran berkas. PNG 9000x9000
                    // berisi bidang polos hanya seperempat megabita namun menuntut
                    // ratusan megabita saat dibuka pengoptimal — dan kehabisan
                    // memori di sana adalah fatal error yang tidak bisa ditangkap.
                    // Ditolak di sini supaya pengurus mendapat pesan yang jelas,
                    // bukan halaman 500 (lihat App\Support\PengoptimalGambar).
                    ->rules(['dimensions:max_width=6000,max_height=6000'])
                    ->validationMessages(['dimensions' => 'Dimensi gambar maksimal 6000 x 6000 piksel. Perkecil dulu sebelum diunggah.'])
                    ->disk('public')
                    ->directory('pelayan-photos')
                    ->imageEditor()
                    ->imageResizeMode('cover')
                    ->imageResizeTargetWidth('800')
                    ->imageResizeTargetHeight('1000')
                    ->maxSize(2048)
                    ->helperText('Maksimal 2 MB. Foto potret paling rapi (rasio 4:5).')
                    ->columnSpanFull(),

                Forms\Components\Select::make('kategori')
                    ->options([
                        'Pendeta' => 'Pendeta',
                        'Parhalado' => 'Parhalado',
                        'Kategorial' => 'Pengurus Kategorial',
                    ])
                    ->required()
                    ->native(false),

                Forms\Components\TextInput::make('jabatan')
                    ->required()
                    ->maxLength(255)
                    ->placeholder('Cth: Pimpinan Jemaat / Sintua Wijk 1 / Ketua Naposobulung'),

                Forms\Components\TextInput::make('bidang')
                    ->datalist([
                        'Dewan Koinonia', 'Dewan Marturia', 'Dewan Diakonia', 'Parartaon',
                        'Sekolah Minggu', 'Remaja & Naposobulung', 'Parompuan', 'Ama', 'Lansia',
                    ])
                    ->placeholder('Contoh: Dewan Koinonia atau Sekolah Minggu')
                    ->maxLength(255),

                Forms\Components\TextInput::make('keterangan')
                    ->maxLength(255)
                    ->placeholder('Cth: Sektor 1 / Aktif'),

                Forms\Components\TextInput::make('telepon')
                    ->tel()
                    ->maxLength(255)
                    ->placeholder('Cth: 08123456789 (Opsional)'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('foto')
                    ->label('')
                    ->disk('public')
                    ->circular(),

                Tables\Columns\TextColumn::make('nama')
                    ->searchable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('kategori')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Pendeta' => 'warning',
                        'Parhalado' => 'success',
                        'Kategorial' => 'info',
                        default => 'gray',
                    })
                    ->searchable(),

                Tables\Columns\TextColumn::make('jabatan')
                    ->searchable(),

                Tables\Columns\TextColumn::make('bidang')
                    ->placeholder('Umum / Lainnya')
                    ->searchable(),

                Tables\Columns\TextColumn::make('keterangan')
                    ->placeholder('—')
                    ->searchable()
                    ->toggleable(),
            ])
            ->defaultSort('nama')
            ->filters([
                Tables\Filters\SelectFilter::make('kategori')
                    ->options([
                        'Pendeta' => 'Pendeta',
                        'Parhalado' => 'Parhalado',
                        'Kategorial' => 'Pengurus Kategorial',
                    ]),
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
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListParhalados::route('/'),
            'create' => Pages\CreateParhalado::route('/create'),
            'edit' => Pages\EditParhalado::route('/{record}/edit'),
        ];
    }
}
