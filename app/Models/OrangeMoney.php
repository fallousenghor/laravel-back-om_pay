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
}
