<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RiwayatMaintenance extends Model
{
    protected $fillable = [
        'perangkat_id',
        'user_id',
        'lokasi_id',
        'nama_pemilik',
        'status_akhir',
        'catatan',
        'foto',
        'deskripsi',
        'harga',
        'tanggal_maintenance',
    ];
    protected $casts = [
        'tanggal_maintenance' => 'date',
        'foto' => 'array',
        'harga' => 'decimal:2',
    ];

    public function perangkat(): BelongsTo
    {
        return $this->belongsTo(Perangkat::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function lokasi()
    {
        return $this->belongsTo(Lokasi::class);
    }

    public function maintenanceTypes()
    {
        return $this->belongsToMany(MaintenanceType::class, 'maintenance_type_riwayat');
    }

    public function komponens()
    {
        return $this->belongsToMany(Komponen::class, 'komponen_riwayat')
            ->withPivot('aksi');
    }
    public function setHargaAttribute($value): void
    {
        if ($value === null || $value === '') {
            $this->attributes['harga'] = null;
            return;
        }

        $clean = is_numeric($value) ? (float) $value : (float) preg_replace('/[^\d.]/', '', (string) $value);
        $this->attributes['harga'] = max(0, $clean);
    }
    public function komponenDetails()
    {
        return $this->hasMany(KomponenRiwayat::class, 'riwayat_maintenance_id')
            ->with('komponen');
    }
    public function getKomponenSummaryAttribute(): string
    {
        if (! $this->relationLoaded('komponenDetails')) {
            $this->load('komponenDetails.komponen');
        }
        return $this->komponenDetails
            ->map(function ($row) {
                $nama = $row->komponen?->nama ?? '-';
                $aksi = $row->aksi ?: '-';
                $ket  = trim((string) $row->keterangan);
                return $ket ? "{$nama} → {$aksi} ({$ket})" : "{$nama} → {$aksi}";
            })
            ->join('; ');
    }
}
