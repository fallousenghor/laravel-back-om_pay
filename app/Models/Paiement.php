<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class Paiement extends Transaction
{
    use HasFactory;

    protected $table = 'paiements';

    protected $fillable = [
        'id_transaction',
        'id_marchand',
        'mode_paiement',
        'details_paiement',
        'id_qr_code',
        'id_code_paiement',
    ];

    protected $casts = [
        'details_paiement' => 'array',
    ];

    // Relationships
    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class, 'id_transaction');
    }

    public function marchand(): BelongsTo
    {
        return $this->belongsTo(Marchand::class, 'id_marchand');
    }

    public function qrCode(): BelongsTo
    {
        return $this->belongsTo(QRCode::class, 'id_qr_code');
    }

    public function codePaiement(): BelongsTo
    {
        return $this->belongsTo(CodePaiement::class, 'id_code_paiement');
    }

    // Scopes
    public function scopeParMode($query, $mode)
    {
        return $query->where('mode_paiement', $mode);
    }

    public function scopeParMarchand($query, $marchandId)
    {
        return $query->where('id_marchand', $marchandId);
    }

    // Methods
    public function scannerQRCode(string $donneesQR): ?array
    {
        // Décoder les données QR et retourner les informations
        return json_decode($donneesQR, true);
    }

    public function saisirCode(string $code): ?CodePaiement
    {
        return CodePaiement::where('code', $code)
            ->where('utilise', false)
            ->where('date_expiration', '>', now())
            ->first();
    }

    public function verifierMarchand(): bool
    {
        return $this->marchand && $this->marchand->actif;
    }

    public function confirmerAvecPin(string $pin): bool
    {
        $utilisateur = $this->transaction->portefeuille->utilisateur;

        if (!$utilisateur->code_pin || !Hash::check($pin, $utilisateur->code_pin)) {
            return false;
        }

        return $this->executerPaiement();
    }

    protected function executerPaiement(): bool
    {
        DB::transaction(function () {
            // Créer la transaction
            $transaction = Transaction::create([
                'id_portefeuille' => $this->transaction->portefeuille->id,
                'type' => 'paiement',
                'montant' => $this->transaction->montant,
                'devise' => $this->transaction->devise,
                'frais' => 0, // Pas de frais pour les paiements
                'reference' => $this->transaction->reference,
                'date_transaction' => now(),
            ]);

            $this->id_transaction = $transaction->id;
            $this->save();

            // Débiter l'utilisateur
            $this->transaction->portefeuille->debiter($this->transaction->montant);

            // Marquer le QR code ou code de paiement comme utilisé
            if ($this->qrCode) {
                $this->qrCode->update(['utilise' => true]);
            }

            if ($this->codePaiement) {
                $this->codePaiement->update(['utilise' => true]);
            }

            $transaction->executer();
        });

        return true;
    }

    public function notifierMarchand(): bool
    {
        // Implémentation de notification au marchand
        return true; // Placeholder
    }

    public function estParQRCode(): bool
    {
        return $this->mode_paiement === 'qr_code';
    }

    public function estParCode(): bool
    {
        return $this->mode_paiement === 'code_numerique';
    }
}
