<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Transaction extends Model
{
    use HasFactory;

    protected $table = 'transactions';

    public $incrementing = false; // UUID, donc pas d'auto-incrément
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

    protected $fillable = [
        'id_utilisateur',
        'type',
        'montant',
        'devise',
        'statut',
        'frais',
        'reference',
        'date_transaction',
        'numero_telephone_destinataire',
        'nom_destinataire',
        'nom_marchand',
        'categorie_marchand',
        'note',
    ];

    protected $casts = [
        'montant' => 'decimal:2',
        'frais' => 'decimal:2',
        'date_transaction' => 'datetime',
    ];

    // Relationships
    public function utilisateur(): BelongsTo
    {
        return $this->belongsTo(Utilisateur::class, 'id_utilisateur');
    }

    public function transfert()
    {
        return $this->hasOne(Transfert::class, 'id_transaction');
    }

    public function paiement()
    {
        return $this->hasOne(Paiement::class, 'id_transaction');
    }

    // Scopes
    public function scopeReussies($query)
    {
        // 'reussie' stored as 'termine' in DB constraint
        return $query->where('statut', 'termine');
    }

    public function scopeEchouees($query)
    {
        return $query->where('statut', 'echouee');
    }

    public function scopeParType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeParPeriode($query, $debut, $fin)
    {
        return $query->whereBetween('date_transaction', [$debut, $fin]);
    }

    public function scopeByUser($query, $userId)
    {
        return $query->where('id_utilisateur', $userId);
    }

    public function scopePending($query)
    {
        return $query->where('statut', 'en_attente');
    }

    public function scopeByReference($query, $reference)
    {
        return $query->where('reference', $reference);
    }

    public function scopeById($query, $id)
    {
        return $query->where('id', $id);
    }

    // Methods
    public function initier(): bool
    {
        $this->reference = $this->genererReference();
        $this->statut = 'en_attente';
        return $this->save();
    }

    public function valider(): bool
    {
        if ($this->statut !== 'en_attente') {
            return false;
        }

        $this->statut = 'en_cours';
        return $this->save();
    }

    public function executer(): bool
    {
        if ($this->statut !== 'en_cours') {
            return false;
        }

        // DB expects 'termine' for a successful transaction
        $this->statut = 'termine';
        return $this->save();
    }

    public function annuler(): bool
    {
        if (in_array($this->statut, ['termine', 'annulee'])) {
            return false;
        }

        $this->statut = 'annulee';
        return $this->save();
    }

    public function genererRecu(): array
    {
        return [
            'reference' => $this->reference,
            'montant' => $this->montant,
            'frais' => $this->frais,
            'date' => $this->date_transaction,
            'statut' => $this->statut,
        ];
    }

    protected function genererReference(): string
    {
        return 'TXN' . strtoupper(Str::random(10)) . time();
    }

    public function estReussie(): bool
    {
        // consider 'termine' as successful per DB constraint
        return $this->statut === 'termine';
    }

    public function estEnCours(): bool
    {
        return $this->statut === 'en_cours';
    }

    public function peutEtreAnnulee(): bool
    {
        return !in_array($this->statut, ['termine', 'annulee']);
    }
}
