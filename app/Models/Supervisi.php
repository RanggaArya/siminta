<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Supervisi extends Model
{
    protected $table = 'supervisi';
    
    protected $fillable = [
        'perangkat_id',
        'user_id',
        'tanggal',
        'keterangan'
    ];
    
    protected $casts = [
        'tanggal' => 'datetime',
    ];
    
    public function perangkat(): BelongsTo
    {
        return $this->belongsTo(Perangkat::class);
    }
    
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}