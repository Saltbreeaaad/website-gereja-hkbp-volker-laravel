<?php

namespace App\Filament\Resources;

use App\Filament\Resources\KasGerejaResource\Pages;
use App\Models\KasGereja;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\RawJs;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class KasGerejaResource extends Resource
{
    protected static ?string $model = KasGereja::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $navigationGroup = 'Administrasi';

    protected static ?string $navigationLabel = 'Kas Gereja';

    protected static ?string $modelLabel = 'Transaksi Kas';

    protected static ?string $pluralModelLabel = 'Kas Gereja';

    protected static ?int $navigationSort = 1;

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
                    ->numeric()
                    ->minValue(1)
                    ->rules(['integer', 'min:1'])
                    ->helperText('Masukkan angka positif. Jenis transaksi menentukan pemasukan atau pengeluaran.'),

                Forms\Components\FileUpload::make('bukti')
                    ->label('Bukti Transaksi (opsional)')
                    ->disk('local')
                    ->directory('bukti-kas')
                    ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png', 'image/webp'])
                    ->maxSize(5120)
                    ->downloadable()
                    ->openable()
                    ->helperText('PDF, JPG, PNG, atau WebP; maksimal 5 MB. Tersimpan privat dan hanya dapat dibuka pengurus.')
                    ->columnSpanFull(),
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
                    // `default` wajib ada. Tanpa arm itu, satu baris berjenis
                    // apa pun di luar kedua nilai ini — hasil impor cadangan,
                    // suntingan SQL langsung, atau nilai baru yang ditambahkan
                    // nanti — melempar UnhandledMatchError dan merobohkan
                    // seluruh halaman kas, bukan sekadar satu selnya.
                    ->color(fn (string $state): string => match ($state) {
                        'Pemasukan' => 'success',
                        'Pengeluaran' => 'danger',
                        default => 'gray',
                    })
                    ->searchable(),

                Tables\Columns\TextColumn::make('keterangan')
                    ->searchable(),

                Tables\Columns\TextColumn::make('nominal')
                    ->money('IDR', locale: 'id')
                    ->sortable()
                    ->summarize(
                        Tables\Columns\Summarizers\Sum::make()
                            ->label('Total')
                            ->money('IDR', locale: 'id')
                    ),

                Tables\Columns\IconColumn::make('bukti')
                    ->label('Bukti')
                    ->boolean()
                    ->getStateUsing(fn (KasGereja $record): bool => filled($record->bukti)),
            ])
            ->defaultSort('tanggal', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('jenis')
                    ->options([
                        'Pemasukan' => 'Pemasukan',
                        'Pengeluaran' => 'Pengeluaran',
                    ]),

                Tables\Filters\Filter::make('periode')
                    ->form([
                        Forms\Components\DatePicker::make('dari')->native(false),
                        Forms\Components\DatePicker::make('sampai')->native(false),
                    ])
                    ->query(fn (Builder $query, array $data): Builder => $query
                        ->when($data['dari'] ?? null, fn (Builder $q, $tgl) => $q->whereDate('tanggal', '>=', $tgl))
                        ->when($data['sampai'] ?? null, fn (Builder $q, $tgl) => $q->whereDate('tanggal', '<=', $tgl)))
                    ->indicateUsing(function (array $data): ?string {
                        if (blank($data['dari'] ?? null) && blank($data['sampai'] ?? null)) {
                            return null;
                        }

                        return 'Periode: '.($data['dari'] ?? 'awal').' s/d '.($data['sampai'] ?? 'kini');
                    }),
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
            'index' => Pages\ListKasGereja::route('/'),
            'create' => Pages\CreateKasGereja::route('/create'),
            'edit' => Pages\EditKasGereja::route('/{record}/edit'),
        ];
    }
}
