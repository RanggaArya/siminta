<?php

namespace App\Filament\Imports;

use App\Models\Perangkat;
use App\Filament\Imports\Traits\MapsMaster;
use App\Support\NomorInventarisGenerator;
use \Maatwebsite\Excel\Concerns\WithHeadingRow;
use \Maatwebsite\Excel\Concerns\ToModel;
use \Maatwebsite\Excel\Concerns\WithBatchInserts;
use \Maatwebsite\Excel\Concerns\WithChunkReading;
use \Maatwebsite\Excel\Concerns\WithValidation;
use \Maatwebsite\Excel\Concerns\SkipsOnFailure;

class PerangkatImporterSelectiveOverwrite
{
    use \Maatwebsite\Excel\Concerns\Importable;
    
    use MapsMaster;

    public function __construct(private array $allowOverwrite = [])
    {
        $this->bootMasterMaps();
        $this->allowOverwrite = array_values(array_unique(array_map(
            fn($n) => strtoupper(trim((string)$n)),
            $this->allowOverwrite
        )));
    }

    public function rules(): array
    {
        return ['nomor_inventaris' => 'nullable|distinct'];
    }

    public function onFailure(\Maatwebsite\Excel\Validators\Failure ...$failures) {}

    public function uniqueBy()
    {
        return 'nomor_inventaris';
    }

    public function model(array $row)
    {
        $row = $this->normalizeRowKeys($row);
        $nama = trim((string)($row['nama_perangkat'] ?? ''));
        if ($nama === '') return null;

        $nomor = $this->normalizeNomor($row['nomor_inventaris'] ?? null);

        $lokasi_id  = $this->getOrCreateId($this->lokasiMap,  \App\Models\Lokasi::class,  'nama_lokasi',  $row['lokasi']  ?? null);
        $status_id  = $this->getOrCreateId($this->statusMap,  \App\Models\Status::class,  'nama_status',  $row['status']  ?? null);
        $kondisi_id = $this->getOrCreateId($this->kondisiMap, \App\Models\Kondisi::class, 'nama_kondisi', $row['kondisi'] ?? null);

        $harga = !empty($row['harga']) ? (int)preg_replace('/\D+/', '', (string)$row['harga']) : null;
        $tanggalDistribusi = $this->parseTanggal($row['tanggal_distribusi'] ?? null);
        $kode = $this->sanitizeKode($row['kode'] ?? null);
        $tahun = !empty($row['tahun_pengadaan']) ? (int)$row['tahun_pengadaan'] : (int)now()->year;

        $jenis_id = null;
        $kategori_id = null;

        if ($nomor && ($parts = $this->parseNomorInventaris($nomor))) {
            $jenis_id    = $this->resolveOrCreateJenisByKode($parts['kode_jenis']);
            $kategori_id = $this->resolveOrCreateKategoriByKode($parts['kode_kat'], $nama);
            $tahun       = $parts['tahun'];
        } else {
            $jenisModel  = $this->resolveOrCreateJenisByName($row['jenis'] ?? 'Hardware');
            $kategori    = $this->resolveKategoriByNamaPerangkat($nama);
            if (!$jenisModel || !$kategori) return null;
            $jenis_id    = (int)$jenisModel->id;
            $kategori_id = (int)$kategori->id;
            $nomor       = NomorInventarisGenerator::generate($jenis_id, $kategori_id, $tahun);
        }

        if ($nomor !== null) {
            $existing = Perangkat::where('nomor_inventaris', $nomor)->first();
            if ($existing) {
                if (!in_array($nomor, $this->allowOverwrite, true)) {
                    return null;
                }

                $existing->fill([
                    'nama_perangkat'     => $nama,
                    'tipe'               => $row['tipe'] ?? null,
                    'spesifikasi'        => $row['spesifikasi'] ?? null,
                    'deskripsi'          => $row['deskripsi'] ?? null,
                    'perolehan'          => $row['perolehan'] ?? null,
                    'tahun_pengadaan'    => $tahun,
                    'harga'              => $harga,
                    'catatan'            => $row['catatan'] ?? null,
                    'mutasi'             => $row['mutasi'] ?? null,
                    'upgrade'            => $row['upgrade'] ?? null,
                    'tanggal_distribusi' => $tanggalDistribusi,
                    'kode'               => $kode,
                    'lokasi_id'          => $lokasi_id,
                    'jenis_id'           => $jenis_id,
                    'status_id'          => $status_id,
                    'kondisi_id'         => $kondisi_id,
                    'kategori_id'        => $kategori_id,
                ]);
                $existing->save();
                return null;
            }
        }

        return new Perangkat([
            'nama_perangkat'     => $nama,
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
            'jenis_id'           => $jenis_id,
            'status_id'          => $status_id,
            'kondisi_id'         => $kondisi_id,
            'kategori_id'        => $kategori_id,
        ]);
    }

    public function batchSize(): int
    {
        return 1000;
    }
    public function chunkSize(): int
    {
        return 1000;
    }
}
