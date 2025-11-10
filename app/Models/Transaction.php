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

    protected $fillable = [
        'id_portefeuille',
        'type',
        'montant',
        'devise',
        'statut',
        'frais',
        'reference',
        'date_transaction',
    ];

    protected $casts = [
        'montant' => 'decimal:2',
        'frais' => 'decimal:2',
        'date_transaction' => 'datetime',
    ];

    // Relationships
    public function portefeuille(): BelongsTo
    {
        return $this->belongsTo(Portefeuille::class, 'id_portefeuille');
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
        return $query->where('statut', 'reussie');
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

        $this->statut = 'reussie';
        return $this->save();
    }

    public function annuler(): bool
    {
        if (in_array($this->statut, ['reussie', 'annulee'])) {
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
        return $this->statut === 'reussie';
    }

    public function estEnCours(): bool
    {
        return $this->statut === 'en_cours';
    }

    public function peutEtreAnnulee(): bool
    {
        return !in_array($this->statut, ['reussie', 'annulee']);
    }
}
