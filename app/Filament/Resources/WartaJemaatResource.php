<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WartaJemaatResource\Pages;
use App\Models\WartaJemaat;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class WartaJemaatResource extends Resource
{
    protected static ?string $model = WartaJemaat::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationGroup = 'Ibadah & Renungan';

    protected static ?string $navigationLabel = 'Warta Jemaat';

    protected static ?string $modelLabel = 'Warta Jemaat';

    protected static ?string $pluralModelLabel = 'Warta Jemaat';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('judul')
                    ->required()
                    ->maxLength(255)
                    ->placeholder('Cth: Warta Jemaat Minggu I Agustus 2026'),

                Forms\Components\DatePicker::make('tanggal')
                    ->required()
                    ->native(false)
                    ->default(now()),

                Forms\Components\FileUpload::make('file_warta')
                    ->label('Dokumen Warta (PDF)')
                    ->acceptedFileTypes(['application/pdf'])
                    ->disk('public')
                    ->directory('warta-jemaat')
                    ->maxSize(10240)
                    ->downloadable()
                    ->openable()
                    ->required()
                    ->helperText('Format PDF, maksimal 10 MB.')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('judul')
                    ->weight('bold')
                    ->wrap()
                    ->searchable(),

                Tables\Columns\TextColumn::make('tanggal')
                    ->date('d F Y')
                    ->sortable(),

                Tables\Columns\IconColumn::make('file_warta')
                    ->label('Berkas')
                    ->boolean()
                    ->getStateUsing(fn (WartaJemaat $record): bool => filled($record->file_warta))
                    ->trueIcon('heroicon-o-document-check')
                    ->falseIcon('heroicon-o-exclamation-triangle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->tooltip(fn (WartaJemaat $record): string => filled($record->file_warta)
                        ? 'Berkas tersedia untuk diunduh jemaat'
                        : 'Berkas belum diunggah — tombol unduh tidak tampil di website'),
            ])
            ->defaultSort('tanggal', 'desc')
            ->actions([
                Tables\Actions\Action::make('unduh')
                    ->label('Unduh')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('gray')
                    ->url(fn (WartaJemaat $record): ?string => $record->urlUnduhan(), shouldOpenInNewTab: true)
                    ->visible(fn (WartaJemaat $record): bool => filled($record->file_warta)),

                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('Belum ada warta jemaat');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListWartaJemaats::route('/'),
            'create' => Pages\CreateWartaJemaat::route('/create'),
            'edit' => Pages\EditWartaJemaat::route('/{record}/edit'),
        ];
    }
}
