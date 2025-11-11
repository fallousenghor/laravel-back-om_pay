<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class OrangeMoney extends Model
{
    use HasFactory;

    protected $table = 'orange_money';

    protected $fillable = [
        'numero_telephone',
        'nom',
        'prenom',
        'numero_cni',
        'solde',
        'status',
        'code',
    ];

    public $incrementing = false; // UUID, donc pas d’auto-incrément
    protected $keyType = 'string'; // la clé primaire est une chaîne (UUID)

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = (string) Str::uuid();
            }
        });
    }

    // Scopes
    public function scopeByPhoneNumber($query, $phoneNumber)
    {
        return $query->where('numero_telephone', $phoneNumber);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeByCode($query, $code)
    {
        return $query->where('code', $code);
    }
}
