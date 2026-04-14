<?php

namespace App\Notifications;

use App\Models\Peminjaman;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PeminjamanDueSoon extends Notification
{
    use Queueable;

    public function __construct(public Peminjaman $peminjaman) {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $p = $this->peminjaman;

        return (new MailMessage)
            ->subject('📅 Pengingat H-3 Pengembalian: ' . ($p->nama_barang ?? 'Perangkat'))
            ->greeting('Halo ' . ($p->pihak_kedua_nama ?? ''))
            ->markdown('emails.peminjaman.due_soon', [
                'peminjaman' => $p,
            ]);
    }
}
