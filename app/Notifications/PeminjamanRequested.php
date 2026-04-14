<?php

namespace App\Notifications;

use App\Models\Peminjaman;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PeminjamanRequested extends Notification
{
  use Queueable;
  public function __construct(public Peminjaman $p) {}

  public function via($notifiable): array
  {
    return ['mail'];
  }

  public function toMail($notifiable): MailMessage
  {
    return (new MailMessage)
      ->subject('🔔 Pengajuan Peminjaman Baru')
      ->greeting('Halo Admin')
      ->line("Pemohon: {$this->p->pihak_kedua_nama}")
      ->line("Barang: {$this->p->nama_barang} ({$this->p->nomor_inventaris})")
      ->line("Periode: {$this->p->tanggal_mulai->format('d/m/Y')} - {$this->p->tanggal_selesai->format('d/m/Y')}")
      ->action('Tinjau Pengajuan', url('/admin/peminjaman'));
  }
}
