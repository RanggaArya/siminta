<?php

namespace App\Filament\Imports\Traits;

use App\Models\JenisPerangkat;
use App\Models\Kondisi;
use App\Models\Lokasi;
use App\Models\Status;
use App\Models\Kategori;
use PhpOffice\PhpSpreadsheet\Shared\Date;

trait MapsMaster
{
    protected array $lokasiMap = [];
    protected array $jenisMap = [];
    protected array $statusMap = [];
    protected array $kondisiMap = [];
    protected array $kategoriMap = [];
    protected array $kategoriCodeMap = [];
    protected array $jenisKodeMap = [];

    // protected array $KATEGORI_NAME_TO_CODE = [
    //     'cpu'              => ['005', 'CPU'],
    //     'monitor'          => ['019', 'Monitor'],
    //     'mouse'            => ['020', 'Mouse'],
    //     'keyboard'         => ['010', 'Keyboard'],
    //     'ups'              => ['035', 'UPS'],
    //     'switch hub'       => ['047', 'Switch Hub'],
    //     'mikrotik'         => ['048', 'Mikrotik'],
    //     'htb'              => ['049', 'HTB'],
    //     'printer label'    => ['043', 'Printer Label'],
    //     'printer thermal'  => ['051', 'Printer Thermal'],
    //     'printer nota'     => ['052', 'Printer Nota'],
    //     'rak server'       => ['040', 'Rak Server'],
    //     'aio'              => ['046', 'AIO'],
    //     'tab'              => ['053', 'Tablet'],
    //     'tablet'           => ['053', 'Tablet'],
    //     // alias:
    //     'printer kso'      => ['043', 'Printer Label'],
    //     'print thermal'    => ['051', 'Printer Thermal'],
    //     'print nota'       => ['052', 'Printer Nota'],
    // ];

    protected array $JENIS_NAME_TO_KODE = [
        'hardware' => '02.4',
    ];

    protected function bootMasterMaps(): void
    {
        $this->lokasiMap  = Lokasi::pluck('id', 'nama_lokasi')->all();
        $this->statusMap  = Status::pluck('id', 'nama_status')->all();
        $this->kondisiMap = Kondisi::pluck('id', 'nama_kondisi')->all();

        $this->kategoriMap = Kategori::pluck('id', 'nama_kategori')->all();

        $this->kategoriCodeMap = [];
        foreach (Kategori::select('id', 'kode_kategori')->get() as $k) {
            $key = $this->normalizeKategoriKode($k->kode_kategori);
            if ($key !== null) {
                $this->kategoriCodeMap[$key] = (int) $k->id;
            }
        }

        $this->jenisMap = JenisPerangkat::all()
            ->pluck('id', 'nama_jenis')
            ->mapWithKeys(fn($id, $name) => [mb_strtolower(trim($name)) => $id])
            ->all();

        $this->jenisKodeMap = JenisPerangkat::pluck('id', 'kode_jenis')->filter()->all();
    }


    protected function getExistingId(array $map, ?string $key): ?int
    {
        $k = trim((string)($key ?? ''));
        if ($k === '') return null;
        $kk = mb_strtolower($k);
        return $map[$kk] ?? null;
    }

    protected function parseTanggal($value): ?string
    {
        if (empty($value)) return null;
        if (is_numeric($value)) {
            try {
                return Date::excelToDateTimeObject($value)->format('Y-m-d');
            } catch (\Throwable) {
                return null;
            }
        }
        $ts = strtotime((string)$value);
        return $ts ? date('Y-m-d', $ts) : null;
    }

    protected function sanitizeKode($value): ?string
    {
        $v = trim((string)($value ?? ''));
        if ($v === '' || $v[0] === '=') return null;
        return $v;
    }

    protected function normalizeNomor(?string $v): ?string
    {
        $n = strtoupper(trim((string)$v));
        $empty = ['', 'NAN', 'NA', 'N/A', '-', '—', '--', '0', '000', '#N/A', 'NULL', '(BLANK)'];
        return ($n === '' || in_array($n, $empty, true)) ? null : $n;
    }

    protected function normalizeRowKeys(array $row): array
    {
        $out = [];
        foreach ($row as $k => $v) {
            $key = strtolower((string)$k);
            $key = str_replace([' ', '-'], '_', $key);
            $aliases = [
                'no_inventaris'  => 'nomor_inventaris',
                'nomor_asset'    => 'nomor_inventaris',
                'no_asset'       => 'nomor_inventaris',
                'tahun'          => 'tahun_pengadaan',
                'tgl_distribusi' => 'tanggal_distribusi',
            ];
            $out[$aliases[$key] ?? $key] = $v;
        }
        return $out;
    }

    protected function getOrCreateId(array &$map, string $modelClass, string $column, $value): ?int
    {
        $name = trim((string)($value ?? ''));
        if ($name === '') return null;

        $lookup = mb_strtolower($name);
        if (isset($map[$lookup])) return (int)$map[$lookup];

        $id = $modelClass::whereRaw("LOWER($column)=?", [$lookup])->value('id');
        if ($id) {
            $map[$lookup] = (int)$id;
            return (int)$id;
        }

        $created = $modelClass::create([$column => $name]);
        $map[$lookup] = (int)$created->id;
        return (int)$created->id;
    }

    protected function normalizeDeviceName(string $name): string
    {
        $n = mb_strtolower(trim($name));

        $drop = ['tp-link', 'tplink', 'tenda', 'mikrotik', 'samsung', 'canon', 'akari', 'panasonic', 'sharp', 'hp', 'epson', 'brother', 'prolink', 'zte', 'huawei', 'xiaomi', 'oppo'];
        $n = preg_replace('/\b(' . implode('|', array_map('preg_quote', $drop)) . ')\b/u', '', $n);
        $n = preg_replace('/\s+/u', ' ', trim($n));

        $n = str_replace(['printer non kso', 'printer kso non'], 'printer label', $n);
        $n = str_replace(['print thermal'], 'printer thermal', $n);
        $n = str_replace(['print nota'], 'printer nota', $n);

        $keywords = [
            'cpu' => 'cpu',
            'monitor' => 'monitor',
            'mouse' => 'mouse',
            'keyboard' => 'keyboard',
            'ups' => 'ups',
            'switch hub' => 'switch hub',
            'mikrotik' => 'mikrotik',
            'htb' => 'htb',
            'printer label' => 'printer label',
            'printer thermal' => 'printer thermal',
            'printer nota' => 'printer nota',
            'rak server' => 'rak server',
            'aio' => 'aio',
            'tablet' => 'tablet',
            'tab' => 'tab',
        ];
        foreach ($keywords as $needle => $norm) {
            if (mb_strpos($n, $needle) !== false) return $norm;
        }
        return $n;
    }

    protected function resolveKategoriByNamaPerangkat(string $namaPerangkat): ?Kategori
    {
        $key = $this->normalizeDeviceName($namaPerangkat);
        if (isset($this->KATEGORI_NAME_TO_CODE[$key])) {
            [$kode, $label] = $this->KATEGORI_NAME_TO_CODE[$key];

            $kategori = Kategori::where('kode_kategori', $kode)->first();
            if ($kategori) {
                $this->kategoriMap[mb_strtolower($kategori->nama_kategori)] = (int)$kategori->id;
                $this->kategoriCodeMap[$kategori->kode_kategori] = (int)$kategori->id;
                return $kategori;
            }

            $created = Kategori::create([
                'nama_kategori' => $label,
                'kode_kategori' => $kode,
            ]);
            $this->kategoriMap[mb_strtolower($created->nama_kategori)] = (int)$created->id;
            $this->kategoriCodeMap[$created->kode_kategori] = (int)$created->id;
            return $created;
        }

        $lookup = mb_strtolower($namaPerangkat);
        if (isset($this->kategoriMap[$lookup])) {
            return Kategori::find($this->kategoriMap[$lookup]);
        }

        $kategori = Kategori::whereRaw('LOWER(nama_kategori)=?', [$lookup])->first();
        if ($kategori) {
            $this->kategoriMap[$lookup] = (int)$kategori->id;
            if ($kategori->kode_kategori) {
                $this->kategoriCodeMap[$kategori->kode_kategori] = (int)$kategori->id;
            }
            return $kategori;
        }

        $kodeBaru = $this->nextKategoriKode();
        $created = Kategori::create([
            'nama_kategori' => $namaPerangkat,
            'kode_kategori' => $kodeBaru,
        ]);
        $this->kategoriMap[mb_strtolower($created->nama_kategori)] = (int)$created->id;
        $this->kategoriCodeMap[$created->kode_kategori] = (int)$created->id;
        return $created;
    }

    protected function parseNomorInventaris(string $ni): ?array
    {
        $re = '/^([A-Z])\.(\d{2}\.\d)\.(\d{3})\.(\d+)\.(\d{4})$/';
        if (!preg_match($re, trim($ni), $m)) return null;

        return [
            'prefix'     => $m[1],
            'kode_jenis' => $m[2],
            'kode_kat'   => $m[3],
            'urut'       => (int)$m[4],
            'tahun'      => (int)$m[5],
        ];
    }


    protected function resolveOrCreateJenisByKode(string $kode): ?int
    {
        $jenis = JenisPerangkat::where('kode_jenis', $kode)->first();

        if (!$jenis) {
            $byName = JenisPerangkat::whereRaw('LOWER(nama_jenis)=?', ['hardware'])->first();
            if ($byName) {
                if (empty($byName->kode_jenis)) {
                    $byName->kode_jenis = $kode;
                    $byName->save();
                }
                $jenis = $byName;
            } else {
                $jenis = JenisPerangkat::create([
                    'nama_jenis' => 'Hardware',
                    'prefix'     => 'B',
                    'kode_jenis' => $kode,
                ]);
            }
        }

        $this->jenisMap[mb_strtolower($jenis->nama_jenis)] = (int)$jenis->id;

        return (int)$jenis->id;
    }

    protected function resolveOrCreateKategoriByKode(string $kodeKat, string $namaPerangkatFallback): ?int
    {
        $kodeKey = $this->normalizeKategoriKode($kodeKat);
        if (!$kodeKey) return null;

        if (isset($this->kategoriCodeMap[$kodeKey])) {
            return $this->kategoriCodeMap[$kodeKey];
        }

        $key   = $this->normalizeDeviceName($namaPerangkatFallback);
        $label = $this->KATEGORI_NAME_TO_CODE[$key][1] ?? $namaPerangkatFallback;

        $created = Kategori::firstOrCreate(
            ['kode_kategori' => $kodeKey],
            ['nama_kategori' => $label]
        );

        $this->kategoriMap[mb_strtolower($created->nama_kategori)] = (int) $created->id;
        $this->kategoriCodeMap[$kodeKey] = (int) $created->id;

        return (int) $created->id;
    }


    protected function normalizeKategoriKode(?string $kode): ?string
    {
        if (!$kode) return null;
        $k = preg_replace('/\D+/', '', $kode);
        if ($k === '') return null;
        return str_pad(substr($k, -3), 3, '0', STR_PAD_LEFT);
    }

    protected function nextKategoriKode(): string
    {
        $max = Kategori::query()->max('kode_kategori');
        $n = (int) preg_replace('/\D+/', '', (string)$max);
        return str_pad($n + 1, 3, '0', STR_PAD_LEFT);
    }

    protected function resolveOrCreateJenisByName(string $nama): ?JenisPerangkat
    {
        $key = mb_strtolower(trim($nama));
        if ($key === '') return null;

        $map = $this->JENIS_NAME_TO_KODE[$key] ?? [null, 'B', $nama];
        $kode = $map[0] ?? null;
        $prefix = $map[1] ?? 'B';
        $label = $map[2] ?? $nama;

        $jenis = JenisPerangkat::whereRaw('LOWER(nama_jenis)=?', [$key])->first();

        if ($jenis) {
            $changed = false;
            if (empty($jenis->kode_jenis) && $kode) {
                $jenis->kode_jenis = $kode;
                $changed = true;
            }
            if (empty($jenis->prefix) && $prefix) {
                $jenis->prefix = $prefix;
                $changed = true;
            }
            if ($changed) $jenis->save();

            $this->jenisMap[$key] = (int)$jenis->id;
            return $jenis; 
        }

        $jenis = JenisPerangkat::create([
            'nama_jenis' => $label,
            'prefix'     => $prefix ?: 'B',
            'kode_jenis' => $kode,
        ]);

        $this->jenisMap[$key] = (int)$jenis->id;
        return $jenis;
    }

    protected function resolveJenisByExcelName(?string $nama): ?int
    {
        $n = trim(mb_strtolower((string)$nama));
        if ($n === '') return null;

        if (isset($this->jenisMap[$n])) return (int)$this->jenisMap[$n];

        $jenis = JenisPerangkat::whereRaw('LOWER(nama_jenis)=?', [$n])->first();

        if ($jenis) {
            $kode = $this->JENIS_NAME_TO_KODE[$n] ?? null;
            if ($kode && empty($jenis->kode_jenis)) {
                $jenis->kode_jenis = $kode;
                $jenis->prefix = $jenis->prefix ?: 'B';
                $jenis->save();
            }
            $this->jenisMap[$n] = (int)$jenis->id;
            return (int)$jenis->id;
        }

        $kode = $this->JENIS_NAME_TO_KODE[$n] ?? null;
        $created = JenisPerangkat::create([
            'nama_jenis' => ucfirst($n),
            'prefix'     => 'B',
            'kode_jenis' => $kode,
        ]);
        $this->jenisMap[$n] = (int)$created->id;
        return (int)$created->id;
    }
    protected function resolveOrUpsertJenisFromNI(string $prefix, string $kodeJenis): int
    {
        $prefix = strtoupper(trim($prefix ?: 'B'));
        $kode   = trim($kodeJenis);

        $jenis = JenisPerangkat::where('kode_jenis', $kode)->first();

        if (!$jenis) {
            $byName = JenisPerangkat::whereRaw('LOWER(nama_jenis)=?', ['hardware'])->first();
            if ($byName) {
                $jenis = $byName;
                if (empty($jenis->kode_jenis)) $jenis->kode_jenis = $kode;
                if (empty($jenis->prefix))     $jenis->prefix     = $prefix;
                $jenis->save();
            } else {
                $jenis = JenisPerangkat::create([
                    'nama_jenis' => 'Hardware',
                    'prefix'     => $prefix,
                    'kode_jenis' => $kode,
                ]);
            }
        } else {
            if (empty($jenis->prefix) || $jenis->prefix !== $prefix) {
                $jenis->prefix = $prefix;
                $jenis->save();
            }
        }

        $this->jenisMap[mb_strtolower($jenis->nama_jenis)] = (int) $jenis->id;

        return (int) $jenis->id;
    }
}
