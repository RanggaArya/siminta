<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Mutasi extends Model
{
    use HasFactory;

    protected $fillable = [
        'perangkat_id',
        'lokasi_mutasi_id',
        'nama_perangkat',
        'nomor_inventaris',
        'tipe',
        'lokasi_asal_id',
        'kondisi_id',
        'tanggal_mutasi',
        'tanggal_diterima',
        'alasan_mutasi',
        'user_id',
    ];

    protected $casts = [
        'tanggal_mutasi' => 'date',
        'tanggal_diterima' => 'date',
    ];

    public function perangkat(): BelongsTo
    {
        return $this->belongsTo(Perangkat::class);
    }

    public function lokasiAsal(): BelongsTo
    {
        return $this->belongsTo(Lokasi::class, 'lokasi_asal_id');
    }

    public function lokasiMutasi(): BelongsTo
    {
        return $this->belongsTo(Lokasi::class, 'lokasi_mutasi_id');
    }

    public function kondisi(): BelongsTo
    {
        return $this->belongsTo(Kondisi::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
