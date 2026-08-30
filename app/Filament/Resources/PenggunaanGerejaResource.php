<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PenggunaanGerejaResource\Pages;
use App\Models\PenggunaanGereja;
use Closure;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PenggunaanGerejaResource extends Resource
{
    protected static ?string $model = PenggunaanGereja::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?string $navigationGroup = 'Administrasi';

    protected static ?string $navigationLabel = 'Penggunaan Gereja';

    protected static ?int $navigationSort = 2;

    protected static ?string $modelLabel = 'Permohonan Penggunaan Gereja';

    protected static ?string $pluralModelLabel = 'Permohonan Penggunaan Gereja';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('kode')
                    ->label('Kode Penelusuran')
                    ->disabled()
                    ->dehydrated(false)
                    ->helperText('Dibuat otomatis. Inilah kode yang dipegang pemohon untuk memeriksa statusnya.')
                    ->visibleOn('edit'),

                Forms\Components\TextInput::make('nama_kegiatan')
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('nama_pemohon')
                    ->label('Nama Pemohon')
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('kontak')
                    ->label('Kontak (WA/Telepon)')
                    ->tel()
                    ->required()
                    ->maxLength(255),

                Forms\Components\DatePicker::make('tanggal')
                    ->required()
                    ->native(false),

                Forms\Components\TimePicker::make('waktu_mulai')
                    ->required()
                    ->seconds(false),

                Forms\Components\TimePicker::make('waktu_selesai')
                    ->required()
                    ->seconds(false)
                    ->after('waktu_mulai'),

                Forms\Components\Textarea::make('keterangan')
                    ->columnSpanFull(),

                Forms\Components\Select::make('status')
                    ->options(array_combine(PenggunaanGereja::STATUS, PenggunaanGereja::STATUS))
                    ->default(PenggunaanGereja::MENUNGGU)
                    ->required()
                    ->native(false)
                    ->rules([
                        fn (Get $get, $record) => function (string $attribute, $value, Closure $fail) use ($get, $record) {
                            if ($value !== PenggunaanGereja::DISETUJUI) {
                                return;
                            }

                            if (! $get('tanggal') || ! $get('waktu_mulai') || ! $get('waktu_selesai')) {
                                return;
                            }

                            if (PenggunaanGereja::hasApprovedConflict($get('tanggal'), $get('waktu_mulai'), $get('waktu_selesai'), $record?->id)) {
                                $fail('Jadwal ini bentrok dengan kegiatan lain yang sudah Disetujui pada tanggal & jam tersebut.');
                            }
                        },
                    ]),

                Forms\Components\TextInput::make('catatan_admin')
                    ->label('Catatan untuk Pemohon (opsional, cth. alasan penolakan)')
                    ->maxLength(255)
                    ->helperText('DIBACA PEMOHON pada halaman lacak permohonan. Tulis alasan yang sopan dan jelas.')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                // Pemohon menyebut kode ini saat menelepon; tanpa kolomnya
                // pengurus harus menebak permohonan yang dimaksud.
                Tables\Columns\TextColumn::make('kode')
                    ->label('Kode')
                    ->fontFamily('mono')
                    ->copyable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('tanggal')
                    ->date('d F Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('jam')
                    ->label('Jam')
                    ->state(fn (PenggunaanGereja $record): string => $record->waktu_mulai->format('H:i').' - '.$record->waktu_selesai->format('H:i')),

                Tables\Columns\TextColumn::make('nama_kegiatan')
                    ->searchable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('nama_pemohon')
                    ->label('Pemohon')
                    ->searchable(),

                Tables\Columns\TextColumn::make('kontak'),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        PenggunaanGereja::DISETUJUI => 'success',
                        PenggunaanGereja::DITOLAK => 'danger',
                        default => 'warning',
                    }),
            ])
            ->defaultSort('tanggal')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(array_combine(PenggunaanGereja::STATUS, PenggunaanGereja::STATUS)),

                Tables\Filters\Filter::make('mendatang')
                    ->label('Hanya jadwal mendatang')
                    ->query(fn (Builder $query) => $query->whereDate('tanggal', '>=', today())),
            ])
            ->actions([
                Tables\Actions\Action::make('setujui')
                    ->label('Setujui')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->visible(fn (PenggunaanGereja $record) => $record->status !== PenggunaanGereja::DISETUJUI)
                    ->requiresConfirmation()
                    ->action(function (PenggunaanGereja $record) {
                        if (PenggunaanGereja::hasApprovedConflict(
                            $record->tanggal->format('Y-m-d'),
                            $record->waktu_mulai->format('H:i'),
                            $record->waktu_selesai->format('H:i'),
                            $record->id
                        )) {
                            Notification::make()
                                ->title('Gagal disetujui')
                                ->body('Bentrok dengan jadwal lain yang sudah Disetujui pada tanggal & jam tersebut.')
                                ->danger()
                                ->send();

                            return;
                        }

                        $record->update(['status' => PenggunaanGereja::DISETUJUI]);

                        Notification::make()->title('Permohonan disetujui')->success()->send();
                    }),

                Tables\Actions\Action::make('tolak')
                    ->label('Tolak')
                    ->icon('heroicon-o-x-mark')
                    ->color('danger')
                    ->visible(fn (PenggunaanGereja $record) => $record->status !== PenggunaanGereja::DITOLAK)
                    ->requiresConfirmation()
                    ->action(fn (PenggunaanGereja $record) => $record->update(['status' => PenggunaanGereja::DITOLAK])),

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

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', PenggunaanGereja::MENUNGGU)->count() ?: null;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPenggunaanGerejas::route('/'),
            'create' => Pages\CreatePenggunaanGereja::route('/create'),
            'edit' => Pages\EditPenggunaanGereja::route('/{record}/edit'),
        ];
    }
}
