<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class QRCode extends Model
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

    protected $table = 'qr_codes';

    protected $fillable = [
        'id_marchand',
        'id_utilisateur',
        'donnees',
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

    public function utilisateur(): BelongsTo
    {
        return $this->belongsTo(Utilisateur::class, 'id_utilisateur');
    }

    public function paiement(): HasOne
    {
        return $this->hasOne(Paiement::class, 'id_qr_code');
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
    public function generer(): string
    {
        // Générer le contenu du QR code (simplifié)
        return json_encode([
            'id' => $this->id,
            'marchand' => $this->marchand->nom,
            'montant' => $this->montant,
            'date_expiration' => $this->date_expiration->timestamp,
        ]);
    }

    public static function decoder(string $donnees): ?array
    {
        $decoded = json_decode($donnees, true);

        if (!$decoded || !isset($decoded['id'])) {
            return null;
        }

        return $decoded;
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
}
