<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Destinataire extends Model
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

    protected $table = 'destinataires';

    protected $fillable = [
        'numero_telephone',
        'nom',
        'operateur',
        'est_valide',
    ];

    protected $casts = [
        'est_valide' => 'boolean',
    ];

    // Relationships
    public function transferts(): HasMany
    {
        return $this->hasMany(Transfert::class, 'id_destinataire');
    }

    // Scopes
    public function scopeValides($query)
    {
        return $query->where('est_valide', true);
    }

    public function scopeParOperateur($query, $operateur)
    {
        return $query->where('operateur', $operateur);
    }

    // Methods
    public function verifier(): bool
    {
        // Vérification basique du numéro de téléphone
        return preg_match('/^[7-8][0-9]{8}$/', $this->numero_telephone);
    }

    public function obtenirInformations(): array
    {
        return [
            'numero_telephone' => $this->numero_telephone,
            'nom' => $this->nom,
            'operateur' => $this->operateur,
            'est_valide' => $this->est_valide,
        ];
    }

    public function marquerCommeValide(): bool
    {
        return $this->update(['est_valide' => true]);
    }

    public function marquerCommeInvalide(): bool
    {
        return $this->update(['est_valide' => false]);
    }

    public function determinerOperateur(): string
    {
        $prefixe = substr($this->numero_telephone, 0, 2);

        return match ($prefixe) {
            '77', '78' => 'orange',
            '76', '80' => 'free',
            '70', '75' => 'expresso',
            default => 'autre',
        };
    }
}
