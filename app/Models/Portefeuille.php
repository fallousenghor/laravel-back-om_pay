<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class Portefeuille extends Model
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

    protected $table = 'portefeuilles';

    protected $fillable = [
        'id_utilisateur',
        'solde',
        'devise',
        'derniere_mise_a_jour',
    ];

    protected $casts = [
        'solde' => 'decimal:2',
        'derniere_mise_a_jour' => 'datetime',
    ];

    // Relationships
    public function utilisateur(): BelongsTo
    {
        return $this->belongsTo(Utilisateur::class, 'id_utilisateur');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'id_portefeuille');
    }

    // Scopes
    public function scopeAvecSoldePositif($query)
    {
        return $query->where('solde', '>', 0);
    }

    public function scopeParDevise($query, $devise)
    {
        return $query->where('devise', $devise);
    }

    // Methods
    public function consulterSolde(): float
    {
        return $this->solde;
    }

    public function verifierFondsSuffisants(float $montant): bool
    {
        return $this->solde >= $montant;
    }

    public function debiter(float $montant): bool
    {
        if (!$this->verifierFondsSuffisants($montant)) {
            return false;
        }

        DB::transaction(function () use ($montant) {
            $this->decrement('solde', $montant);
            $this->update(['derniere_mise_a_jour' => now()]);
        });

        return true;
    }

    public function crediter(float $montant): bool
    {
        DB::transaction(function () use ($montant) {
            $this->increment('solde', $montant);
            $this->update(['derniere_mise_a_jour' => now()]);
        });

        return true;
    }

    public function calculerSoldeApresTransaction(float $montant, string $type = 'debit'): float
    {
        return $type === 'debit' ? $this->solde - $montant : $this->solde + $montant;
    }

    public function transfererVers(Portefeuille $destinataire, float $montant): bool
    {
        if (!$this->verifierFondsSuffisants($montant)) {
            return false;
        }

        DB::transaction(function () use ($destinataire, $montant) {
            $this->debiter($montant);
            $destinataire->crediter($montant);
        });

        return true;
    }
}
