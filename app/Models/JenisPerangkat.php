<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JenisPerangkat extends Model
{
    protected $fillable = [
        'nama_jenis',
        'prefix',
        'kode_jenis',
    ];  
}
