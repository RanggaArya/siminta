<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PenarikanAlat extends Model
{
    use HasFactory;

    protected $table = 'penarikan_alats';

    protected $fillable = [
        'perangkat_id',
        'nama_perangkat',
        'nomor_inventaris',
        'tipe',
        'spesifikasi',
        'lokasi_id',
        'tahun_pembelian',
        'tanggal_penarikan',
        'alasan_penarikan',
        'alasan_lainnya',
        'tindak_lanjut_tipe',
        'tindak_lanjut_detail',
        'user_id',
    ];

    protected $casts = [
        'tanggal_penarikan' => 'date',
        'alasan_penarikan' => 'array',
    ];

    public function perangkat(): BelongsTo
    {
        return $this->belongsTo(Perangkat::class);
    }

    public function lokasi(): BelongsTo
    {
        return $this->belongsTo(Lokasi::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
