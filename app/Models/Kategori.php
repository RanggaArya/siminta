<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kategori extends Model
{
    protected $fillable = ['nama_kategori','kode_kategori','masa_pakai_tahun'];

    public function perangkats()
    {
        return $this->hasMany(Perangkat::class);
    }
}
