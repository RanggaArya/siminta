<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;
use App\Support\NomorInventarisGenerator;
use App\Models\Kategori;
use Illuminate\Support\Carbon;


class Perangkat extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama_perangkat',
        'nomor_inventaris',
        'kode',
        'tipe',
        'spesifikasi',
        'deskripsi',
        'perolehan',
        'tahun_pengadaan',
        'harga',
        'tanggal_distribusi',
        'catatan',
        'mutasi',
        'upgrade',
        'jenis_id',
        'kategori_id',
        'lokasi_id',
        'status_id',
        'kondisi_id',
    ];

    protected $casts = [
        'tahun_pengadaan'   => 'integer',
        'harga'             => 'integer',
        'tanggal_distribusi' => 'date',
        'created_at'        => 'datetime',
        'updated_at'        => 'datetime',
    ];

    protected $with = [];
    protected $perPage = 25;

    public function setHargaAttribute($value): void
    {
        if ($value === null || $value === '') {
            $this->attributes['harga'] = null;
            return;
        }
        $clean = is_numeric($value) ? (string)$value : preg_replace('/\D+/', '', (string) $value);
        $this->attributes['harga'] = max(0, (int) $clean);
    }

    public function setTahunPengadaanAttribute($value): void
    {
        if ($value === null || $value === '') {
            $this->attributes['tahun_pengadaan'] = null;
            return;
        }

        $year = (int) $value;
        $min  = 1990;
        $max  = (int) now()->addYear()->year;
        $this->attributes['tahun_pengadaan'] = max($min, min($max, $year));
    }

    public function setNomorInventarisAttribute($value): void
    {
        $trim = strtoupper(trim((string) $value));
        $emptyTokens = ['', 'NAN', 'NA', 'N/A', '-', '—', '--', '0', '000', '#N/A', 'NULL', '(BLANK)'];
        if (in_array($trim, $emptyTokens, true)) {
            $this->attributes['nomor_inventaris'] = null;
            return;
        }
        $this->attributes['nomor_inventaris'] = $trim;
    }

    /**
     * Hitung tahun kedaluwarsa.
     */
    public function getTahunExpiredAttribute(): ?int
    {
        // Memastikan relasi kategori dimuat dan memiliki tahun pengadaan
        if (!$this->relationLoaded('kategori') || !$this->kategori || !$this->tahun_pengadaan) {
            return null;
        }

        // Asumsi kolom masa pakai di model Kategori sudah di-update menjadi 'masa_pakai_tahun'
        $masaPakai = (int) $this->kategori->masa_pakai_tahun;
        return (int) $this->tahun_pengadaan + $masaPakai;
    }

    /**
     * Periksa apakah perangkat sudah expired.
     */
    public function isExpired(): bool
    {
        $tahunExpired = $this->tahun_expired;

        if ($tahunExpired === null) {
            return false;
        }

        $tahunSaatIni = Carbon::now()->year;

        // Perangkat expired jika tahun saat ini >= tahun expired
        return $tahunSaatIni >= $tahunExpired;
    }

    protected static function booted(): void
    {
        static::creating(function (Perangkat $m) {
            if (!$m->kategori_id && $m->nama_perangkat) {
                $nama = trim($m->nama_perangkat);
                $katId = Kategori::whereRaw('LOWER(nama_kategori)=?', [mb_strtolower($nama)])->value('id');
                if (!$katId) {
                    $max = Kategori::max('kode_kategori');
                    $n   = (int) preg_replace('/\D+/', '', (string) $max);
                    $kode = str_pad($n + 1, 3, '0', STR_PAD_LEFT);
                    $kat = Kategori::create(['nama_kategori' => $nama, 'kode_kategori' => $kode]);
                    $katId = $kat->id;
                }
                $m->kategori_id = $katId;
            }

            if (empty($m->nomor_inventaris) && $m->jenis_id && $m->kategori_id) {
                $tahun = $m->tahun_pengadaan ?: (int) date('Y');
                $m->nomor_inventaris = NomorInventarisGenerator::generate(
                    (int)$m->jenis_id,
                    (int)$m->kategori_id,
                    (int)$tahun
                );
            }
        });

        static::saving(function (Perangkat $perangkat) {
            // Perlu memastikan relasi kategori dimuat sebelum mengaksesnya
            if (!$perangkat->relationLoaded('kategori')) {
                $perangkat->load('kategori');
            }

            // Hanya jalankan jika perangkat memiliki kategori, tahun pengadaan, dan harga > 0
            if ($perangkat->kategori && $perangkat->tahun_pengadaan && $perangkat->harga > 0) {
                if ($perangkat->isExpired()) {
                    // Otomatis ubah harga menjadi 0 jika sudah expired
                    $perangkat->harga = 0;
                }
            }
        });
    }

    public function lokasi(): BelongsTo
    {
        return $this->belongsTo(Lokasi::class);
    }

    public function kategori()
    {
        return $this->belongsTo(Kategori::class);
    }
    public function jenis()
    {
        return $this->belongsTo(\App\Models\JenisPerangkat::class, 'jenis_id');
    }
    public function status(): BelongsTo
    {
        return $this->belongsTo(Status::class);
    }
    public function kondisi(): BelongsTo
    {
        return $this->belongsTo(Kondisi::class);
    }

    public function riwayatMaintenances(): HasMany
    {
        return $this->hasMany(RiwayatMaintenance::class);
    }

    public function maintenanceTerakhir(): HasOne
    {
        return $this->hasOne(RiwayatMaintenance::class)->latestOfMany();
    }

    public function scopeAktif(Builder $q): Builder
    {
        return $q->whereHas('status', fn($qq) => $qq->where('nama_status', 'Aktif'));
    }
    public function scopePerbaikan(Builder $q): Builder
    {
        return $q->whereHas('status', fn($qq) => $qq->where('nama_status', 'Perbaikan'));
    }
    public function peminjamans()
    {
        return $this->hasMany(\App\Models\Peminjaman::class);
    }
}
