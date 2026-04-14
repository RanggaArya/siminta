<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Crypt;

class Setting extends Model
{
    use HasFactory;
    protected $fillable = [
        'key',
        'value',
    ];
    public const ENCRYPTED_KEYS = [
        'export_password',
        'export_owner_password',
    ];
    public function setValueAttribute($value): void
    {
        if (in_array($this->attributes['key'] ?? null, self::ENCRYPTED_KEYS, true)) {
            $this->attributes['value'] = is_null($value) || $value === ''
                ? null
                : Crypt::encryptString($value);
            return;
        }

        $this->attributes['value'] = $value;
    }
    public function getValueAttribute($value)
    {
        if (in_array($this->attributes['key'] ?? null, self::ENCRYPTED_KEYS, true)) {
            return $value ? Crypt::decryptString($value) : null;
        }

        return $value;
    }
}
