<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class Transfert extends Transaction
{
    use HasFactory;

    protected $table = 'transferts';

    protected $fillable = [
        'id_transaction',
        'id_expediteur',
        'id_destinataire',
        'numero_telephone_destinataire',
        'nom_destinataire',
        'note',
        'date_expiration',
    ];

    protected $casts = [
        'date_expiration' => 'datetime',
    ];

    // Relationships
    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class, 'id_transaction');
    }

    public function expediteur(): BelongsTo
    {
        return $this->belongsTo(Utilisateur::class, 'id_expediteur');
    }

    public function destinataireOrangeMoney()
    {
        return $this->belongsTo(OrangeMoney::class, 'numero_telephone_destinataire', 'numero_telephone');
    }

    // Scopes
    public function scopeExpires($query)
    {
        return $query->where('date_expiration', '<=', now());
    }

    public function scopeParExpediteur($query, $expediteurId)
    {
        return $query->where('id_expediteur', $expediteurId);
    }

    // Methods
    public function verifierDestinataire(): bool
    {
        return $this->destinataireOrangeMoney && $this->destinataireOrangeMoney->est_actif;
    }

    public function calculerFrais(): float
    {
        // Frais de 1% avec minimum de 50 FCFA
        $frais = $this->transaction->montant * 0.01;
        return max($frais, 50);
    }

    public function confirmerAvecPin(string $pin): bool
    {
        if (!$this->expediteur->code_pin || !Hash::check($pin, $this->expediteur->code_pin)) {
            return false;
        }

        return $this->executerTransfert();
    }

    protected function executerTransfert(): bool
    {
        DB::transaction(function () {
            // Créer la transaction
            $transaction = Transaction::create([
                'id_portefeuille' => $this->expediteur->portefeuille->id,
                'type' => 'transfert',
                'montant' => $this->transaction->montant,
                'devise' => $this->transaction->devise,
                'frais' => $this->calculerFrais(),
                'reference' => $this->transaction->reference,
                'date_transaction' => now(),
            ]);

            $this->id_transaction = $transaction->id;
            $this->save();

            // Débiter l'expéditeur
            $this->expediteur->portefeuille->debiter($this->transaction->montant + $transaction->frais);

            // Créditer le destinataire (logique simplifiée)
            // Dans un vrai système, il faudrait gérer le portefeuille du destinataire

            $transaction->executer();
        });

        return true;
    }

    public function notifierDestinataire(): bool
    {
        // Implémentation de notification (SMS, push, etc.)
        return true; // Placeholder
    }

    public function estExpire(): bool
    {
        return $this->date_expiration && $this->date_expiration <= now();
    }

    public function peutEtreAnnule(): bool
    {
        return !$this->estExpire() && $this->transaction && $this->transaction->peutEtreAnnulee();
    }
}
