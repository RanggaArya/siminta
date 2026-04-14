<?php

namespace App\Filament\Imports;

use App\Models\Perangkat;
use App\Models\JenisPerangkat;
use App\Models\Kategori;
use App\Models\Lokasi;
use App\Models\Status;
use App\Models\Kondisi;
use App\Filament\Imports\Traits\MapsMaster;
use App\Support\NomorInventarisGenerator;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\WithUpserts;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Validators\Failure;

class PerangkatImporterOverwrite implements
    ToModel,
    WithHeadingRow,
    WithBatchInserts,
    WithChunkReading,
    WithValidation,
    SkipsOnFailure,
    WithUpserts
{
    use Importable;
    use MapsMaster;

    /**
     * @var array<string, true>
     */
    private array $seenNomorInventaris = [];

    public function __construct()
    {
        $this->bootMasterMaps();
    }

    public function rules(): array
    {
        return [
            'nomor_inventaris' => 'nullable|distinct',
        ];
    }

    public function uniqueBy()
    {
        return 'nomor_inventaris';
    }

    public function onFailure(Failure ...$failures)
    {
    }

    public function model(array $row)
    {
        $row = $this->normalizeRowKeys($row);
        $namaPerangkat = trim((string)($row['nama_perangkat'] ?? ''));
        if ($namaPerangkat === '') {
            return null;
        }

        $nomor = $this->normalizeNomor($row['nomor_inventaris'] ?? null);

        if ($nomor !== null) {
            if (isset($this->seenNomorInventaris[$nomor])) {
                return null;
            }
        }

        $lokasi_id  = $this->getOrCreateId($this->lokasiMap,  Lokasi::class,  'nama_lokasi',  $row['lokasi'] ?? null);
        $status_id  = $this->getOrCreateId($this->statusMap,  Status::class,  'nama_status',  $row['status'] ?? null);
        $kondisi_id = $this->getOrCreateId($this->kondisiMap, Kondisi::class, 'nama_kondisi', $row['kondisi'] ?? null);

        $harga = !empty($row['harga']) ? (int)preg_replace('/\D+/', '', (string)$row['harga']) : null;
        $tanggalDistribusi = $this->parseTanggal($row['tanggal_distribusi'] ?? null);
        $kode = isset($row['kode']) ? (trim((string)$row['kode']) ?: null) : null;

        $jenis_id = null;
        $kategori_id = null;
        $tahun = (int) (now()->year);

        if ($nomor && ($parts = $this->parseNomorInventaris($nomor))) {
            $jenis_id    = $this->resolveOrCreateJenisByKode($parts['kode_jenis']);
            $kategori_id = $this->resolveOrCreateKategoriByKode($parts['kode_kat'], $namaPerangkat);
            $tahun       = $parts['tahun'];
        } else {
            $kategori_model = $this->resolveKategoriByNamaPerangkat($namaPerangkat);
            $jenis_model    = $this->resolveOrCreateJenisByName($row['jenis'] ?? 'Hardware');
            $tahun = !empty($row['tahun_pengadaan']) ? (int)$row['tahun_pengadaan'] : (int)now()->year;

            if (!$jenis_model || !$kategori_model) {
                return null; 
            }

            $jenis_id = $jenis_model->id;
            $kategori_id = $kategori_model->id;

            $nomor = NomorInventarisGenerator::generateFromCodes(
                $jenis_id,
                $jenis_model->prefix,
                $jenis_model->kode_jenis,
                $kategori_id,
                $kategori_model->kode_kategori,
                $tahun
            );
        }

        if ($nomor) {
            if (isset($this->seenNomorInventaris[$nomor])) {
                return null; 
            }
            $this->seenNomorInventaris[$nomor] = true;
        }

        return new Perangkat([
            'nama_perangkat'     => $namaPerangkat,
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