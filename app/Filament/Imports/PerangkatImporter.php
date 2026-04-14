<?php

namespace App\Filament\Imports;

use App\Models\Perangkat;
use App\Models\Lokasi;
use App\Models\Status;
use App\Models\Kondisi;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Validators\Failure;
use App\Filament\Imports\Traits\MapsMaster;

class PerangkatImporter implements
    ToCollection,
    WithHeadingRow,
    WithValidation,
    SkipsOnFailure
{
    use Importable;
    use MapsMaster;

    private array $seenNomorInventaris = [];

    private array $nextUrutMap = [];

    public function __construct()
    {
        $this->bootMasterMaps();
    }

    public function rules(): array
    {
        return [
            'nomor_inventaris' => 'nullable|unique:perangkats,nomor_inventaris',
        ];
    }

    public function onFailure(Failure ...$failures) {}

    public function collection(Collection $rows)
    {
        $rows = $rows->values()->map(function ($row, $i) {
            $r = $this->normalizeRowKeys((array) $row);
            $r['__row_index']    = $i;
            $r['nama_perangkat'] = trim((string)($r['nama_perangkat'] ?? ''));
            return $r;
        })->filter(fn($r) => $r['nama_perangkat'] !== '');

        $withNI = [];
        $toGen  = [];

        foreach ($rows as $row) {
            $nomor = $this->normalizeNomor($row['nomor_inventaris'] ?? null);

            if ($nomor) {
                if (isset($this->seenNomorInventaris[$nomor])) continue;

                if (Perangkat::where('nomor_inventaris', $nomor)->exists()) {
                    $this->seenNomorInventaris[$nomor] = true;
                    continue;
                }

                $this->seenNomorInventaris[$nomor] = true;
                $withNI[] = [$row, $nomor];
            } else {
                $toGen[] = $row;
            }
        }

        foreach ($withNI as [$row, $nomor]) {
            $this->persistRow($row, $nomor);
        }

        if (empty($toGen)) return;

        $prepared = collect($toGen)->map(function ($row) {
            $namaPerangkat = $row['nama_perangkat'];

            $kategori = $this->resolveKategoriByNamaPerangkat($namaPerangkat);
            $jenis    = $this->resolveOrCreateJenisByName($row['jenis'] ?? 'Hardware');
            $tahun    = !empty($row['tahun_pengadaan']) ? (int)$row['tahun_pengadaan'] : (int) now()->year;

            if (!$kategori || !$jenis) return null;

            return array_merge($row, [
                '_prefix'   => strtoupper($jenis->prefix ?: 'B'),
                '_kodeJ'    => $jenis->kode_jenis ?: '02.4',
                '_kodeK'    => str_pad(preg_replace('/\D+/', '', (string) $kategori->kode_kategori), 3, '0', STR_PAD_LEFT),
                '_jenis_id' => $jenis->id,
                '_kat_id'   => $kategori->id,
                '_tahun'    => $tahun,
                '_nama_key' => mb_strtolower($this->normalizeDeviceName($namaPerangkat)),
            ]);
        })->filter()->values();

        if ($prepared->isEmpty()) return;

        DB::transaction(function () use ($prepared) {
            $prepared->groupBy(fn($r) => "{$r['_prefix']}|{$r['_kodeJ']}|{$r['_kodeK']}")
                ->each(function (Collection $group, string $key) {

                    if (!isset($this->nextUrutMap[$key])) {
                        [$p, $kj, $kk] = explode('|', $key);
                        $like = "{$p}.{$kj}.{$kk}.%";
                        $maxUrut = (int) DB::table('perangkats')
                            ->where('nomor_inventaris', 'like', $like)
                            ->selectRaw("
                                MAX(CAST(
                                    SUBSTRING_INDEX(
                                        SUBSTRING_INDEX(nomor_inventaris, '.', -2), '.', 1
                                    ) AS UNSIGNED)
                                ) AS max_urut
                            ")
                            ->value('max_urut');
                        $this->nextUrutMap[$key] = $maxUrut;
                    }

                    $sorted = $group->sortBy([
                        ['_nama_key', 'asc'],
                        ['_tahun', 'asc'],
                        ['__row_index', 'asc'],
                    ])->values();

                    foreach ($sorted as $row) {
                        $urut = ++$this->nextUrutMap[$key];

                        $width = max(2, strlen((string) $urut));
                        $urutStr = str_pad((string) $urut, $width, '0', STR_PAD_LEFT);

                        $nomor = "{$row['_prefix']}.{$row['_kodeJ']}.{$row['_kodeK']}.{$urutStr}.{$row['_tahun']}";

                        while (
                            isset($this->seenNomorInventaris[$nomor]) ||
                            DB::table('perangkats')->where('nomor_inventaris', $nomor)->exists()
                        ) {

                            $urut = ++$this->nextUrutMap[$key];
                            $width = max(2, strlen((string) $urut));
                            $urutStr = str_pad((string) $urut, $width, '0', STR_PAD_LEFT);
                            $nomor = "{$row['_prefix']}.{$row['_kodeJ']}.{$row['_kodeK']}.{$urutStr}.{$row['_tahun']}";
                        }

                        $this->seenNomorInventaris[$nomor] = true;

                        $this->persistRow($row, $nomor);
                    }
                });
        });
    }

    private function persistRow(array $row, string $nomor): void
    {
        $lokasi_id  = $this->getOrCreateId($this->lokasiMap,  Lokasi::class,  'nama_lokasi',  $row['lokasi'] ?? null);
        $status_id  = $this->getOrCreateId($this->statusMap,  Status::class,  'nama_status',  $row['status'] ?? null);
        $kondisi_id = $this->getOrCreateId($this->kondisiMap, Kondisi::class, 'nama_kondisi', $row['kondisi'] ?? null);

        $harga = !empty($row['harga']) ? (int)preg_replace('/\D+/', '', (string)$row['harga']) : null;
        $tanggalDistribusi = $this->parseTanggal($row['tanggal_distribusi'] ?? null);
        $kode = isset($row['kode']) ? (trim((string)$row['kode']) ?: null) : null;

        $tahun       = (int)($row['_tahun'] ?? ($row['tahun_pengadaan'] ?? now()->year));
        $jenis_id    = (int)($row['_jenis_id'] ?? $this->resolveJenisByExcelName($row['jenis'] ?? 'hardware'));
        $kategori_id = (int)($row['_kat_id'] ?? optional($this->resolveKategoriByNamaPerangkat($row['nama_perangkat']))->id);

        Perangkat::create([
            'nama_perangkat'     => $row['nama_perangkat'],
            'tipe'               => $row['tipe'] ?? null,
            'spesifikasi'        => $row['spesifikasi'] ?? null,
            'deskripsi'          => $row['deskripsi'] ?? null,
            'perolehan'          => $row['perolehan'] ?? null,
            'tahun_pengadaan'    => $tahun,
            'nomor_inventaris'   => $nomor,
            'harga'              => $harga,
            'catatan'            => $row['catatan'] ?? null,
            'mutasi'             => $row['mutasi'] ?? null,
            'upgrade'            => $row['upgrade'] ?? null,
            'tanggal_distribusi' => $tanggalDistribusi,
            'kode'               => $kode,
            'lokasi_id'          => $lokasi_id,
            'jenis_id'           => $jenis_id ?: null,
            'status_id'          => $status_id,
            'kondisi_id'         => $kondisi_id,
            'kategori_id'        => $kategori_id ?: null,
        ]);
    }
}
