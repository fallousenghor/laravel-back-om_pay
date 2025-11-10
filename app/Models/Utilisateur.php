<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use Illuminate\Support\Str;

class Utilisateur extends Model
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

    protected $table = 'utilisateurs';

    protected $fillable = [
        'numero_telephone',
        'prenom',
        'nom',
        'email',
        'code_pin',
        'otp',
        'otp_expires_at',
        'numero_cni',
        'statut_kyc',
        'biometrie_activee',
        'date_creation',
        'derniere_connexion',
    ];

    protected $hidden = [
        'code_pin',
    ];

    protected $casts = [
        'biometrie_activee' => 'boolean',
        'date_creation' => 'datetime',
        'derniere_connexion' => 'datetime',
        'otp_expires_at' => 'datetime',
    ];

    // Relationships
    public function authentification(): HasOne
    {
        return $this->hasOne(Authentification::class, 'id_utilisateur');
    }

    public function parametresSecurite(): HasOne
    {
        return $this->hasOne(ParametresSecurite::class, 'id_utilisateur');
    }

    public function portefeuille(): HasOne
    {
        return $this->hasOne(Portefeuille::class, 'id_utilisateur');
    }

    public function contacts(): HasMany
    {
        return $this->hasMany(Contact::class, 'id_utilisateur');
    }

    public function transfertsEnvoyes(): HasMany
    {
        return $this->hasMany(Transfert::class, 'id_expediteur');
    }

    public function paiements(): HasMany
    {
        return $this->hasMany(Paiement::class, 'id_utilisateur');
    }

    // Scopes
    public function scopeActifs($query)
    {
        return $query->where('statut_kyc', 'verifie');
    }

    public function scopeParOperateur($query, $operateur)
    {
        return $query->where('numero_telephone', 'like', $operateur . '%');
    }

    public function scopeKycEnCours($query)
    {
        return $query->where('statut_kyc', 'en_cours');
    }

    // Methods
    public function inscrire(array $data): self
    {
        $data['code_pin'] = Hash::make($data['code_pin']);
        $data['date_creation'] = now();

        return self::create($data);
    }

    public function seConnecter(): bool
    {
        $this->update(['derniere_connexion' => now()]);
        return true;
    }

    public function seDeconnecter(): bool
    {
        // Logique de déconnexion si nécessaire
        return true;
    }

    public function verifierOTP(string $code): bool
    {
        // Implémentation de vérification OTP
        return true; // Placeholder
    }

    public function mettreAJourProfil(array $data): bool
    {
        return $this->update($data);
    }

    public function changerCodePin(string $ancienPin, string $nouveauPin): bool
    {
        if (!Hash::check($ancienPin, $this->code_pin)) {
            return false;
        }

        $this->update(['code_pin' => Hash::make($nouveauPin)]);
        return true;
    }

    public function activerBiometrie(): bool
    {
        return $this->update(['biometrie_activee' => true]);
    }

    public function getNomCompletAttribute(): string
    {
        return $this->prenom . ' ' . $this->nom;
    }

    public function estBloque(): bool
    {
        return $this->parametresSecurite?->date_deblocage > now();
    }

    public function peutFaireTransaction(float $montant): bool
    {
        return $this->portefeuille && $this->portefeuille->solde >= $montant;
    }
}
