<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

class Peminjaman extends Model
{
    use HasFactory;

    protected $table = 'peminjamans';

    protected $fillable = [
        'pihak_pertama_nama',
        'pihak_pertama_jabatan',
        'pihak_pertama_unit',
        'pihak_kedua_nama',
        'pihak_kedua_jabatan',
        'pihak_kedua_unit',
        'perangkat_id',
        'nama_barang',
        'merk',
        'nomor_inventaris',
        'kondisi_terakhir',
        'alasan_pinjam',
        'tanggal_mulai',
        'tanggal_selesai',
        'status',
        'catatan',

        'peminjam_email',
        'reminder_h3_sent_at',
        'requested_by_user_id',
        'approved_by_user_id',
        'approved_at',
        'rejected_at',
    ];

    protected $casts = [
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
        'reminder_h3_sent_at' => 'datetime',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
    ];

    public function perangkat(): BelongsTo
    {
        return $this->belongsTo(Perangkat::class);
    }
    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by_user_id');
    }
    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by_user_id');
    }

    protected static function booted(): void
    {
        static::creating(function (Peminjaman $p) {
            if (Auth::check()) {
                $u = Auth::user();
                $p->requested_by_user_id ??= $u->id;
                $p->pihak_kedua_nama   ??= $u->name;
                $p->peminjam_email     ??= $u->email;
                $p->status             ??= 'Menunggu';
            }
        });
    }
    public function getRejectedReasonAttribute(): ?string
    {
        if (! $this->catatan) return null;

        if (preg_match('/Ditolak:\s*(.*)/i', (string) $this->catatan, $m)) {
            return trim($m[1]) ?: null;
        }

        return null;
    }
}
