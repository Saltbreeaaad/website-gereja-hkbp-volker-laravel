<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

/**
 * Kelola akun pengurus dari dalam panel.
 *
 * Tanpa halaman ini, menambah bendahara baru menuntut akses SSH dan `tinker` —
 * sesuatu yang tidak dimiliki pengurus gereja. Terlihat hanya oleh
 * administrator; lihat App\Policies\UserPolicy.
 */
class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-circle';

    protected static ?string $navigationGroup = 'Administrasi';

    protected static ?string $navigationLabel = 'Akun Pengurus';

    protected static ?string $modelLabel = 'Akun Pengurus';

    protected static ?string $pluralModelLabel = 'Akun Pengurus';

    protected static ?int $navigationSort = 9;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Nama')
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('email')
                    ->email()
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),

                Forms\Components\Select::make('role')
                    ->label('Peran')
                    ->options(User::PERAN)
                    ->default(User::SEKRETARIS)
                    ->required()
                    ->native(false)
                    ->helperText('Administrator: seluruh akses, termasuk menghapus data dan mengelola akun. '
                        .'Bendahara: mengelola kas gereja. Sekretaris: mengelola jadwal, renungan, warta, '
                        .'galeri, pelayan, dan permohonan gedung.')
                    // Administrator terakhir tidak boleh menurunkan perannya sendiri:
                    // panel akan kehilangan satu-satunya orang yang bisa mengembalikannya.
                    ->disabled(fn (?User $record): bool => $record?->is(Auth::user()) && static::hanyaSatuAdmin())
                    ->dehydrated(),

                Forms\Components\TextInput::make('password')
                    ->password()
                    ->revealable()
                    ->minLength(8)
                    ->maxLength(255)
                    ->required(fn (string $operation): bool => $operation === 'create')
                    ->helperText('Minimal 8 karakter. Kosongkan saat menyunting bila kata sandi tidak diganti.')
                    // Kolom kosong saat menyunting berarti "jangan diubah", bukan
                    // "kosongkan kata sandinya".
                    ->dehydrated(fn (?string $state): bool => filled($state)),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama')
                    ->weight('bold')
                    ->searchable(),

                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->copyable(),

                Tables\Columns\TextColumn::make('role')
                    ->label('Peran')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => User::PERAN[$state] ?? $state)
                    ->color(fn (string $state): string => match ($state) {
                        User::ADMIN => 'danger',
                        User::BENDAHARA => 'warning',
                        default => 'info',
                    }),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->date('d F Y')
                    ->sortable()
                    ->toggleable(),
            ])
            ->defaultSort('name')
            ->filters([
                Tables\Filters\SelectFilter::make('role')
                    ->label('Peran')
                    ->options(User::PERAN),
            ])
            ->actions([
                Tables\Actions\Action::make('akhiri_sesi')
                    ->label('Akhiri Semua Sesi')
                    ->icon('heroicon-o-lock-closed')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalDescription('Akun ini akan keluar dari seluruh perangkat dan harus masuk kembali.')
                    ->action(function (User $record): void {
                        $jumlah = $record->akhiriSemuaSesi();

                        Notification::make()
                            ->title($jumlah > 0 ? "{$jumlah} sesi diakhiri" : 'Tidak ada sesi aktif')
                            ->success()
                            ->send();
                    }),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    // Pagar kedua di atas UserPolicy::delete(): tombolnya pun tidak
                    // muncul untuk akun sendiri maupun administrator terakhir.
                    ->visible(fn (User $record): bool => ! $record->is(Auth::user())
                        && ! ($record->isAdmin() && static::hanyaSatuAdmin())),
            ])
            ->emptyStateHeading('Belum ada akun pengurus lain');
    }

    /**
     * `protected`, bukan `private`.
     *
     * Dipanggil lewat `static::`, dan pemanggilan itu tidak aman pada metode
     * privat: begitu resource ini diturunkan, `static::` menunjuk kelas anak
     * yang tidak dapat melihat metode privat induknya, dan panggilannya gagal
     * di runtime — bukan saat kompilasi.
     */
    protected static function hanyaSatuAdmin(): bool
    {
        return User::query()->where('role', User::ADMIN)->count() <= 1;
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
