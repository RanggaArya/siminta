<?php

namespace App\Notifications;

use App\Models\PengajuanMaintenance;
use Illuminate\Bus\Queueable;
// use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use NotificationChannels\Telegram\TelegramChannel;
use NotificationChannels\Telegram\TelegramMessage;

class PengajuanMaintenanceNotification extends Notification
{
  use Queueable;
  public function __construct(public PengajuanMaintenance $p) {}
  public function via($notifiable): array
  {
    return [TelegramChannel::class];
  }

//   public function toMail($notifiable): MailMessage
//   {
//     return (new MailMessage)
//       ->subject('✅ Pengajuan Maintenance')
//       ->greeting("Halo Teknisi")
//       ->line("Pengajuan Maintenance untuk {$this->p->nama_perangkat} tipe {$this->p->tipe}")
//       ->line("Keterangan {$this->p->keterangan}")
//       ->line("Silakan menghubungi pihak terkait untuk menindaklanjuti");
//   }

  public function toTelegram($notifiable): TelegramMessage
  {
    $chatId = config('services.telegram_default_chat_id');
    $lokasiName = $this->p->lokasi?->nama_lokasi ?? '-';
    $userName = $this->p->user?->name ?? '-';
    $noInventaris = $this->p->perangkats?->nomor_inventaris ?? '-';

    return TelegramMessage::create()
      ->to($chatId)
      ->content("*MAINTENANCE  IT*\n\n" .
          "*Pengajuan Maintenance oleh : {$userName}*\n" .
          "No. Inventaris : *{$noInventaris}*\n" .
          "Perangkat       : *{$this->p->nama_perangkat}*\n" .
          "Tipe            : *{$this->p->tipe}*\n" .
          "Lokasi            : {$lokasiName}\n" .
          "Keterangan     : {$this->p->keterangan}\n"
      )
      ->options(['parse_mode' => 'Markdown']);
  }
}
