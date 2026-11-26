<?php

namespace App\Notifications;

use App\Models\Pesanan;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class StatusPesananBerubah extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(private Pesanan $pesanan)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Status pesanan {$this->pesanan->kode_pesanan}")
            ->line("Status pesanan Anda berubah menjadi {$this->pesanan->status->label()}.")
            ->action('Lihat Pesanan', url('/pesanan/' . $this->pesanan->kode_pesanan));
    }

    public function toArray(object $notifiable): array
    {
        return [];
    }
}
