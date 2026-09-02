<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PeriodeKasResource\Pages;
use App\Models\PeriodeKas;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Support\RawJs;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class PeriodeKasResource extends Resource
{
    protected static ?string $model = PeriodeKas::class;

    protected static ?string $navigationIcon = 'heroicon-o-lock-closed';

    protected static ?string $navigationGroup = 'Administrasi';

    protected static ?string $navigationLabel = 'Periode Kas';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('periode')
                ->label('Periode (YYYY-MM)')
                ->placeholder(now()->format('Y-m'))
                ->regex('/^\d{4}-(0[1-9]|1[0-2])$/')
                ->unique(ignoreRecord: true)
                ->required(),
            Forms\Components\TextInput::make('saldo_awal')
                ->prefix('Rp')
                ->mask(RawJs::make('$money($input, \',\', \'.\', 0)'))
                ->stripCharacters('.')
                ->numeric()
                ->default(0)
                ->required(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('periode')->label('Periode')->sortable(),
                Tables\Columns\TextColumn::make('saldo_awal')->money('IDR', locale: 'id'),
                Tables\Columns\IconColumn::make('ditutup_at')->label('Ditutup')->boolean()->getStateUsing(fn (PeriodeKas $record): bool => $record->sudahDitutup()),
                Tables\Columns\TextColumn::make('penutup.name')->label('Ditutup oleh')->placeholder('—'),
                Tables\Columns\TextColumn::make('ditutup_at')->label('Waktu tutup')->dateTime('d M Y, H:i')->placeholder('Masih terbuka'),
            ])
            ->defaultSort('periode', 'desc')
            ->actions([
                Tables\Actions\Action::make('tutup')
                    ->label('Tutup Periode')
                    ->icon('heroicon-o-lock-closed')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->visible(fn (PeriodeKas $record): bool => ! $record->sudahDitutup())
                    ->action(function (PeriodeKas $record): void {
                        $record->update(['ditutup_at' => now(), 'ditutup_oleh' => Auth::id()]);
                        Notification::make()->title('Periode dikunci')->success()->send();
                    }),
                Tables\Actions\Action::make('buka')
                    ->label('Buka Kembali')
                    ->icon('heroicon-o-lock-open')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn (PeriodeKas $record): bool => $record->sudahDitutup() && Auth::user()?->isAdmin())
                    ->action(fn (PeriodeKas $record) => $record->update(['ditutup_at' => null, 'ditutup_oleh' => null])),
                Tables\Actions\EditAction::make()->visible(fn (PeriodeKas $record): bool => ! $record->sudahDitutup()),
            ])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPeriodeKas::route('/'),
            'create' => Pages\CreatePeriodeKas::route('/create'),
            'edit' => Pages\EditPeriodeKas::route('/{record}/edit'),
        ];
    }
}
