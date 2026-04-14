<?php

namespace App\Console\Commands;

use App\Models\Peminjaman;
use App\Notifications\PeminjamanDueSoon;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;

class SendPeminjamanH3Reminder extends Command
{
    protected $signature = 'peminjaman:reminder-h3';
    protected $description = 'Kirim email pengingat H-3 dari tanggal_selesai untuk peminjaman yang masih Dipinjam';

    public function handle(): int
    {
        $todayStr = Carbon::now('Asia/Jakarta')->toDateString();

        $peminjamans = Peminjaman::query()
            ->whereRaw('DATEDIFF(tanggal_selesai, ?) = 3', [$todayStr])
            ->where('status', 'Dipinjam')
            ->whereNull('reminder_h3_sent_at')
            ->get();

        $sent = 0;
        foreach ($peminjamans as $p) {
            $email = $p->peminjam_email;
            if (!$email) continue;

            Notification::route('mail', $email)->notify(new PeminjamanDueSoon($p));

            $p->forceFill(['reminder_h3_sent_at' => now('Asia/Jakarta')])->save();
            $sent++;
        }

        $this->info("Pengingat H-3 terkirim: {$sent}");
        return self::SUCCESS;
    }
}
