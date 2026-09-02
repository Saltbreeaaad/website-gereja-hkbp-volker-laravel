<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GaleriResource\Pages;
use App\Models\Galeri;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class GaleriResource extends Resource
{
    protected static ?string $model = Galeri::class;

    protected static ?string $navigationIcon = 'heroicon-o-photo';

    protected static ?string $navigationGroup = 'Dokumentasi';

    protected static ?string $navigationLabel = 'Galeri Kegiatan';

    protected static ?string $modelLabel = 'Foto Galeri';

    protected static ?string $pluralModelLabel = 'Galeri Kegiatan';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('judul')
                    ->required()
                    ->maxLength(255)
                    ->placeholder('Cth: Perayaan Paskah & Perjamuan Kudus'),

                Forms\Components\TextInput::make('kategori')
                    ->required()
                    ->default('Umum')
                    ->maxLength(80)
                    ->datalist(['Ibadah', 'Pelayanan', 'Kategorial', 'Perayaan', 'Sosial', 'Umum'])
                    ->helperText('Dipakai pengunjung untuk menyaring galeri.'),

                Forms\Components\DatePicker::make('tanggal')
                    ->label('Tanggal Kegiatan')
                    ->required()
                    ->native(false)
                    ->default(now()),

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
                    ->directory('galeri-photos')
                    ->imageEditor()
                    ->imageResizeMode('cover')
                    ->imageResizeTargetWidth('1600')
                    ->imageResizeTargetHeight('1067')
                    ->maxSize(4096)
                    ->required()
                    ->helperText('JPG, PNG, atau WebP; maksimal 4 MB. Foto diubah ke WebP, metadata dibuang, dan thumbnail dibuat otomatis.')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('foto')
                    ->label('')
                    ->disk('public')
                    ->height(56),

                Tables\Columns\TextColumn::make('judul')
                    ->weight('bold')
                    ->wrap()
                    ->searchable(),

                Tables\Columns\TextColumn::make('tanggal')
                    ->date('d F Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('kategori')
                    ->badge()
                    ->searchable(),
            ])
            ->defaultSort('tanggal', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('kategori')
                    ->options(fn (): array => Galeri::query()->distinct()->orderBy('kategori')->pluck('kategori', 'kategori')->all()),
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
            ->emptyStateHeading('Belum ada foto galeri');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListGaleris::route('/'),
            'create' => Pages\CreateGaleri::route('/create'),
            'edit' => Pages\EditGaleri::route('/{record}/edit'),
        ];
    }
}
