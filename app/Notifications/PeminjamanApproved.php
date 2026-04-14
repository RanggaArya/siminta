<?php 

namespace App\Notifications;

use App\Models\Peminjaman;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PeminjamanApproved extends Notification
{
    use Queueable;
    public function __construct(public Peminjaman $p) {}
    public function via($notifiable): array { return ['mail']; }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('✅ Pengajuan Peminjaman Disetujui')
            ->greeting("Halo {$this->p->pihak_kedua_nama}")
            ->line("Pengajuan untuk {$this->p->nama_barang} disetujui. Status: Dipinjam.")
            ->line("Silakan mengambil barang sesuai prosedur.");
    }
}