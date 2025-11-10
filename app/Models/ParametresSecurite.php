<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class ParametresSecurite extends Model
{
    use HasFactory;

    protected $table = 'parametres_securites';

    protected $fillable = [
        'id_utilisateur',
        'biometrie_active',
        'tentatives_echouees',
        'date_deblocage',
    ];

    protected $casts = [
        'biometrie_active' => 'boolean',
        'date_deblocage' => 'datetime',
    ];

    // Relationships
    public function utilisateur(): BelongsTo
    {
        return $this->belongsTo(Utilisateur::class, 'id_utilisateur');
    }

    // Scopes
    public function scopeBloques($query)
    {
        return $query->where('date_deblocage', '>', now());
    }

    public function scopeBiometrieActivee($query)
    {
        return $query->where('biometrie_active', true);
    }

    // Methods
    public function verifierBlocage(): bool
    {
        return $this->date_deblocage && $this->date_deblocage > now();
    }

    public function incrementerTentatives(): bool
    {
        $this->increment('tentatives_echouees');

        if ($this->tentatives_echouees >= 3) {
            return $this->bloquerCompte();
        }

        return true;
    }

    public function reinitialiserTentatives(): bool
    {
        return $this->update(['tentatives_echouees' => 0]);
    }

    public function bloquerCompte(): bool
    {
        $dateDeblocage = now()->addHours(24); // Blocage de 24h
        return $this->update([
            'date_deblocage' => $dateDeblocage,
            'tentatives_echouees' => 0,
        ]);
    }

    public function debloquerCompte(): bool
    {
        return $this->update([
            'date_deblocage' => null,
            'tentatives_echouees' => 0,
        ]);
    }

    public function activerBiometrie(): bool
    {
        return $this->update(['biometrie_active' => true]);
    }

    public function desactiverBiometrie(): bool
    {
        return $this->update(['biometrie_active' => false]);
    }
}
