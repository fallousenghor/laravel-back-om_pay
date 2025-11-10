<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class CodePaiement extends Model
{
    use HasFactory;
    
    protected $keyType = 'string';
    public $incrementing = false;

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($model) {
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = Str::uuid()->toString();
            }
        });
    }

    protected $table = 'code_paiements';

    protected $fillable = [
        'code',
        'id_marchand',
        'montant',
        'date_generation',
        'date_expiration',
        'utilise',
    ];

    protected $casts = [
        'montant' => 'decimal:2',
        'date_generation' => 'datetime',
        'date_expiration' => 'datetime',
        'utilise' => 'boolean',
    ];

    // Relationships
    public function marchand(): BelongsTo
    {
        return $this->belongsTo(Marchand::class, 'id_marchand');
    }

    public function paiement(): HasOne
    {
        return $this->hasOne(Paiement::class, 'id_code_paiement');
    }

    // Scopes
    public function scopeNonUtilises($query)
    {
        return $query->where('utilise', false);
    }

    public function scopeValides($query)
    {
        return $query->where('utilise', false)
            ->where('date_expiration', '>', now());
    }

    public function scopeExpires($query)
    {
        return $query->where('date_expiration', '<=', now());
    }

    // Methods
    public static function generer(): string
    {
        return strtoupper(Str::random(6));
    }

    public function valider(): bool
    {
        return !$this->utilise && $this->date_expiration > now();
    }

    public function verifierExpiration(): bool
    {
        return $this->date_expiration <= now();
    }

    public function marquerCommeUtilise(): bool
    {
        return $this->update(['utilise' => true]);
    }

    public function obtenirInformations(): array
    {
        return [
            'code' => $this->code,
            'montant' => $this->montant,
            'marchand' => $this->marchand->nom,
            'date_expiration' => $this->date_expiration,
            'utilise' => $this->utilise,
        ];
    }
}
