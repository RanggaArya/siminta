<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventoryNumberCounter extends Model
{
    protected $fillable = ['jenis_id','kategori_id','tahun','last_number'];
}
