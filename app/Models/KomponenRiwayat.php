<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KomponenRiwayat extends Model
{
    protected $table = 'komponen_riwayat';
    public $timestamps = false;

    protected $fillable = [
        'riwayat_maintenance_id',
        'komponen_id',
        'aksi',
        'keterangan',
    ];

    public function riwayat()
    {
        return $this->belongsTo(RiwayatMaintenance::class, 'riwayat_maintenance_id');
    }

    public function komponen()
    {
        return $this->belongsTo(Komponen::class);
    }
}
