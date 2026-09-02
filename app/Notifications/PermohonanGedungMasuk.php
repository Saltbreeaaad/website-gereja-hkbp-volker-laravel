<?php

namespace App\Notifications;

use App\Filament\Resources\PenggunaanGerejaResource;
use App\Models\PenggunaanGereja;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Queue\SerializesModels;

/**
 * Beri tahu pengurus bahwa ada permohonan pemakaian gedung yang masuk.
 *
 * Salurannya `database`, bukan `mail`: panel Filament sudah menampilkan
 * notifikasi database lewat lonceng di kanan atas, dan itu bekerja tanpa
 * konfigurasi SMTP apa pun. Menambah saluran `mail` cukup dengan menambah
 * 'mail' pada via() setelah kredensial SMTP gereja tersedia.
 *
 * Diantre. Pemicunya adalah formulir publik, dan `Notification::send()`
 * yang berjalan langsung memuat seluruh akun pengurus lalu menulis satu
 * baris notifikasi per orang sebelum pemohon menerima balasannya —
 * pekerjaan yang tidak ada hubungannya dengan apa yang ia tunggu.
 * Membutuhkan `queue:work`, yang dijadwalkan di routes/console.php.
 */
class PermohonanGedungMasuk extends Notification implements ShouldQueue
{
    use Queueable, SerializesModels;

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
