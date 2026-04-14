<?php

namespace App\Console\Commands;

use App\Models\Perangkat;
use App\Models\JenisPerangkat;
use App\Models\Kategori;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection as ECollection;
use Illuminate\Support\Facades\DB;

class ResequenceInventaris extends Command
{
    protected $signature = 'inventaris:resequnits 
        {--simulate : Dry-run, hanya tampilkan rencana perubahan}
        {--only-missing : Hanya generate untuk baris yang nomor_inventaris-nya kosong}
        {--chunk=2000 : Ukuran batch update tiap commit}';

    protected $description = 'Resequence nomor urut unit berdasarkan (nama_perangkat ASC, tahun_pengadaan ASC) per (prefix, kode_jenis, kode_kategori).';

    public function handle(): int
    {
        $simulate = (bool) $this->option('simulate');
        $onlyMissing = (bool) $this->option('only-missing');
        $chunk = (int) $this->option('chunk');
        $this->info('Memuat data perangkat...');
        $rows = Perangkat::query()
            ->leftJoin('jenis_perangkats as jp', 'jp.id', '=', 'perangkats.jenis_id')
            ->leftJoin('kategoris as kt', 'kt.id', '=', 'perangkats.kategori_id')
            ->select([
                'perangkats.id',
                'perangkats.nama_perangkat',
                'perangkats.tahun_pengadaan',
                'perangkats.nomor_inventaris',
                'perangkats.created_at',
                DB::raw("UPPER(COALESCE(jp.prefix, 'B')) as _prefix"),
                DB::raw("COALESCE(jp.kode_jenis, '02.4') as _kodeJ"),
                DB::raw("LPAD(REGEXP_REPLACE(COALESCE(kt.kode_kategori, '000'), '\\\\D+', ''), 3, '0') as _kodeK"),
            ])
            ->get();

        if ($rows->isEmpty()) {
            $this->warn('Tidak ada data.');
            return self::SUCCESS;
        }

        $grouped = $rows->groupBy(function ($r) {
            return "{$r->_prefix}|{$r->_kodeJ}|{$r->_kodeK}";
        });

        $totalChanged = 0;
        $updatesBuffer = [];

        foreach ($grouped as $key => $group) {
            [$prefix, $kodeJ, $kodeK] = explode('|', $key);

            $sorted = $group->sortBy([
                ['nama_perangkat', 'asc'],
                ['tahun_pengadaan', 'asc'],
                ['created_at', 'asc'],
                ['id', 'asc'],
            ])->values();

            $n = $sorted->count();
            $width = max(2, strlen((string)$n));

            $urut = 0;
            foreach ($sorted as $row) {
                if ($onlyMissing && !empty($row->nomor_inventaris)) {
                    continue;
                }

                $urut++;
                $urutStr = str_pad((string)$urut, $width, '0', STR_PAD_LEFT);

                $tahun = (int)($row->tahun_pengadaan ?: date('Y'));
                if (!$row->tahun_pengadaan && $row->nomor_inventaris) {
                    if (preg_match('/\.(\d{4})$/', $row->nomor_inventaris, $m)) {
                        $tahun = (int)$m[1];
                    }
                }

                $newNI = "{$prefix}.{$kodeJ}.{$kodeK}.{$urutStr}.{$tahun}";

                if ($row->nomor_inventaris === $newNI) {
                    continue;
                }

                $updatesBuffer[] = [
                    'id' => $row->id,
                    'nomor_inventaris' => $newNI,
                ];

                if ($simulate) {
                    $this->line("{$row->id} :: {$row->nomor_inventaris}  ->  {$newNI}");
                }
            }
        }

        if ($simulate) {
            $this->info("SIMULASI selesai. Rencana update: " . count($updatesBuffer));
            return self::SUCCESS;
        }

        if (empty($updatesBuffer)) {
            $this->info('Tidak ada perubahan.');
            return self::SUCCESS;
        }

        $this->info('Menulis perubahan...');
        DB::transaction(function () use (&$updatesBuffer, $chunk, &$totalChanged) {
            foreach (array_chunk($updatesBuffer, $chunk) as $batch) {
                foreach ($batch as $u) {
                    Perangkat::whereKey($u['id'])->update(['nomor_inventaris' => $u['nomor_inventaris']]);
                    $totalChanged++;
                }
            }
        });

        $this->info("Selesai. Row ter-update: {$totalChanged}");
        return self::SUCCESS;
    }
}
