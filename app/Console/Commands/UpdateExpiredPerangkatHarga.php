<?php

namespace App\Console\Commands;

use App\Models\Perangkat;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class UpdateExpiredPerangkatHarga extends Command
{
    protected $signature = 'inventaris:update-expired-harga';
    protected $description = 'Updates the price of expired Perangkat to 0.';

    public function handle()
    {
        $this->info('Mulai pengecekan perangkat yang expired...');
        $currentYear = Carbon::now()->year;
        $updatedCount = 0;

        // Ambil perangkat yang harganya > 0 dan memiliki tahun pengadaan
        $perangkats = Perangkat::query()
            ->where('harga', '>', 0)
            ->whereNotNull('tahun_pengadaan')
            ->with('kategori') // Load relasi kategori untuk isExpired()
            ->get();

        $this->withProgressBar($perangkats, function (Perangkat $perangkat) use ($currentYear, &$updatedCount) {
            // Menggunakan logika isExpired() dari model Perangkat
            if ($perangkat->isExpired()) {
                // Set harga menjadi 0
                $perangkat->harga = 0;
                // Gunakan saveQuietly untuk menghindari triggering events lain secara berlebihan
                $perangkat->saveQuietly(); 
                $updatedCount++;
            }
        });

        $this->newLine();
        $this->info("Selesai. Total {$updatedCount} perangkat di-update harganya menjadi Rp. 0.");
        return Command::SUCCESS;
    }
}