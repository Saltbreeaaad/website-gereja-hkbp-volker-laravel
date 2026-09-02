<?php

namespace App\Notifications;

use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class CadanganBermasalah extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(private readonly string $pesan) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return FilamentNotification::make()
            ->title('Cadangan basis data perlu diperiksa')
            ->body($this->pesan)
            ->danger()
            ->icon('heroicon-o-exclamation-triangle')
            ->getDatabaseMessage();
    }
}
