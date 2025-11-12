<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class SessionOmpay extends Model
{
    protected $table = 'sessions_ompay';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'utilisateur_id',
        'token',
        'refresh_token',
        'last_activity'
    ];

    protected $casts = [
        'last_activity' => 'datetime'
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            $model->id = Str::uuid();
        });
    }

    public function utilisateur()
    {
        return $this->belongsTo(Utilisateur::class);
    }
}