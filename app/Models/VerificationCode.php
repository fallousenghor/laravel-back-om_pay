<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class VerificationCode extends Model
{
    protected $table = 'verification_codes';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'numero_telephone',
        'code',
        'token',
        'expire_at',
        'used'
    ];

    protected $casts = [
        'expire_at' => 'datetime',
        'used' => 'boolean'
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            $model->id = Str::uuid();
        });
    }

    public function isValid()
    {
        return !$this->used && $this->expire_at->isFuture();
    }
}