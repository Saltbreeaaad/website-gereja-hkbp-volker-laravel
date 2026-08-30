<?php

namespace App\Notifications;

use App\Filament\Resources\PenggunaanGerejaResource;
use App\Models\PenggunaanGereja;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

/**
 * Beri tahu pengurus bahwa ada permohonan pemakaian gedung yang masuk.
 *
 * Salurannya `database`, bukan `mail`: panel Filament sudah menampilkan
 * notifikasi database lewat lonceng di kanan atas, dan itu bekerja tanpa
 * konfigurasi SMTP apa pun. Menambah saluran `mail` cukup dengan menambah
 * 'mail' pada via() setelah kredensial SMTP gereja tersedia.
 */
class PermohonanGedungMasuk extends Notification
{
    use Queueable;

    public function __construct(private readonly PenggunaanGereja $permohonan) {}

    /** @return list<string> */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /** @return array<string, mixed> */
    public function toArray(object $notifiable): array
    {
        return FilamentNotification::make()
            ->title('Permohonan pemakaian gedung baru')
            ->body(sprintf(
                '%s — %s, %s. Diajukan oleh %s.',
                $this->permohonan->nama_kegiatan,
                $this->permohonan->tanggal->translatedFormat('d F Y'),
                $this->permohonan->waktu_mulai->format('H:i').'–'.$this->permohonan->waktu_selesai->format('H:i'),
                $this->permohonan->nama_pemohon,
            ))
            ->icon('heroicon-o-calendar-days')
            ->warning()
            ->actions([
                Action::make('tinjau')
                    ->label('Tinjau permohonan')
                    ->url(PenggunaanGerejaResource::getUrl('edit', ['record' => $this->permohonan]))
                    ->markAsRead(),
            ])
            ->getDatabaseMessage();
    }
}
