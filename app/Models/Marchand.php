<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Marchand extends Model
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

    protected $table = 'marchands';

    protected $fillable = [
        'nom',
        'numero_telephone',
        'adresse',
        'logo',
        'actif',
        'accepte_qr',
        'accepte_code',
    ];

    protected $casts = [
        'actif' => 'boolean',
        'accepte_qr' => 'boolean',
        'accepte_code' => 'boolean',
    ];

    // Relationships
    public function qrCodes(): HasMany
    {
        return $this->hasMany(QRCode::class, 'id_marchand');
    }

    public function codesPaiement(): HasMany
    {
        return $this->hasMany(CodePaiement::class, 'id_marchand');
    }

    public function paiements(): HasMany
    {
        return $this->hasMany(Paiement::class, 'id_marchand');
    }

    // Scopes
    public function scopeActifs($query)
    {
        return $query->where('actif', true);
    }

    public function scopeAcceptantQR($query)
    {
        return $query->where('accepte_qr', true);
    }

    public function scopeAcceptantCode($query)
    {
        return $query->where('accepte_code', true);
    }

    public function scopeById($query, $id)
    {
        return $query->where('id', $id);
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('categorie', $category);
    }

    // Methods
    public function obtenirInformations(): array
    {
        return [
            'id' => $this->id,
            'nom' => $this->nom,
            'numero_telephone' => $this->numero_telephone,
            'adresse' => $this->adresse,
            'logo' => $this->logo,
            'actif' => $this->actif,
            'accepte_qr' => $this->accepte_qr,
            'accepte_code' => $this->accepte_code,
        ];
    }

    public function genererQRCode(float $montant): QRCode
    {
        $donnees = json_encode([
            'marchand_id' => $this->id,
            'montant' => $montant,
            'timestamp' => now()->timestamp,
        ]);

        return $this->qrCodes()->create([
            'donnees' => $donnees,
            'montant' => $montant,
            'date_expiration' => now()->addMinutes(30),
        ]);
    }

    public function genererCodePaiement(float $montant): CodePaiement
    {
        $code = strtoupper(Str::random(6));

        return $this->codesPaiement()->create([
            'code' => $code,
            'montant' => $montant,
            'date_expiration' => now()->addMinutes(15),
        ]);
    }

    public function validerPaiement(Paiement $paiement): bool
    {
        // Logique de validation du paiement
        return $paiement->transaction && $paiement->transaction->estReussie();
    }

    public function activer(): bool
    {
        return $this->update(['actif' => true]);
    }

    public function desactiver(): bool
    {
        return $this->update(['actif' => false]);
    }
}
