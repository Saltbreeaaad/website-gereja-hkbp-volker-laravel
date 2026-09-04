<?php

namespace App\Notifications;

use App\Filament\Resources\PermohonanDoaResource;
use App\Models\PermohonanDoa;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Queue\SerializesModels;

/**
 * Beri tahu pengurus bahwa ada pokok doa baru — tanpa membawa isinya.
 *
 * Sebelum ini permohonan doa adalah satu-satunya formulir publik yang masuk
 * diam-diam: permohonan gedung mengisi lonceng panel, pokok doa tidak, jadi
 * doanya hanya terbaca bila seseorang kebetulan membuka menu Permohonan Doa.
 * Untuk jemaat yang menitipkan pokok doa, keterlambatan itu adalah bedanya
 * antara didoakan dan tidak.
 *
 * **Isi doanya sengaja tidak ikut.** Halaman /doa menjanjikan formulir privat
 * yang hanya terbuka bagi administrator dan sekretaris lewat panel. Baris
 * notifikasi adalah salinan kedua yang tidak ikut dijaga policy mana pun: ia
 * tersimpan di tabel `notifications`, terbawa ke setiap cadangan, dan muncul
 * di lonceng yang sering terbuka di layar saat orang lain ikut melihat. Nama
 * pengirim pun tidak dibawa dengan alasan yang sama. Yang perlu diketahui
 * pengurus dari sebuah notifikasi hanyalah bahwa ada yang harus dibuka.
 *
 * Penerimanya disamakan dengan yang boleh membacanya (PermohonanDoaPolicy):
 * administrator dan sekretaris. Bendahara tidak menindaklanjuti pokok doa.
 *
 * Diantre dengan alasan yang sama seperti PermohonanGedungMasuk — lihat
 * catatan di sana.
 */
class PermohonanDoaMasuk extends Notification implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(private readonly PermohonanDoa $permohonan) {}

    /** @return list<string> */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /** @return array<string, mixed> */
    public function toArray(object $notifiable): array
    {
        return FilamentNotification::make()
            ->title('Permohonan doa baru')
            ->body('Satu pokok doa masuk lewat halaman Doa. Isinya sengaja tidak dibawa ke notifikasi ini — bukalah di panel.')
            ->icon('heroicon-o-heart')
            ->info()
            ->actions([
                Action::make('buka')
                    ->label('Buka permohonan')
                    ->url(PermohonanDoaResource::getUrl('edit', ['record' => $this->permohonan]))
                    ->markAsRead(),
            ])
            ->getDatabaseMessage();
    }
}
