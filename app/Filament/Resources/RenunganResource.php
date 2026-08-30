<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RenunganResource\Pages;
use App\Models\Renungan;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class RenunganResource extends Resource
{
    protected static ?string $model = Renungan::class;

    protected static ?string $navigationIcon = 'heroicon-o-book-open';

    protected static ?string $navigationGroup = 'Ibadah & Renungan';

    protected static ?string $navigationLabel = 'Renungan Harian';

    protected static ?string $modelLabel = 'Renungan';

    protected static ?string $pluralModelLabel = 'Renungan';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('judul')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),

                Forms\Components\DatePicker::make('tanggal')
                    ->required()
                    ->native(false)
                    ->default(now())
                    ->helperText('Renungan tampil di halaman publik sesuai tanggal ini.'),

                Forms\Components\TextInput::make('penulis')
                    ->maxLength(255)
                    ->placeholder('Kosongkan untuk "Tim Pelayanan"'),

                Forms\Components\FileUpload::make('foto')
                    ->label('Gambar Ilustrasi')
                    ->image()
                    ->disk('public')
                    ->directory('renungan-photos')
                    ->imageEditor()
                    ->imageResizeMode('cover')
                    ->imageResizeTargetWidth(1200)
                    ->imageResizeTargetHeight(630)
                    ->maxSize(2048)
                    ->helperText('Maksimal 2 MB. Ukuran ideal 1200x630 piksel.')
                    ->columnSpanFull(),

                Forms\Components\Textarea::make('isi')
                    ->label('Isi Renungan')
                    ->required()
                    ->rows(14)
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
                    ->square(),

                Tables\Columns\TextColumn::make('judul')
                    ->weight('bold')
                    ->wrap()
                    ->searchable(),

                Tables\Columns\TextColumn::make('tanggal')
                    ->date('d F Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('penulis')
                    ->placeholder('Tim Pelayanan')
                    ->searchable(),
            ])
            ->defaultSort('tanggal', 'desc')
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('Belum ada renungan');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRenungans::route('/'),
            'create' => Pages\CreateRenungan::route('/create'),
            'edit' => Pages\EditRenungan::route('/{record}/edit'),
        ];
    }
}
