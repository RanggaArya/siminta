<?php 

namespace App\Notifications;

use App\Models\Peminjaman;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PeminjamanRejected extends Notification
{
    use Queueable;
    public function __construct(public Peminjaman $p, public ?string $reason = null) {}
    public function via($notifiable): array { return ['mail']; }

    public function toMail($notifiable): MailMessage
    {
        $msg = (new MailMessage)
            ->subject('❌ Pengajuan Peminjaman Ditolak')
            ->greeting("Halo {$this->p->pihak_kedua_nama}")
            ->line("Maaf, pengajuan Anda ditolak.");
        if ($this->reason) $msg->line("Alasan: {$this->reason}");
        return $msg;
    }
}