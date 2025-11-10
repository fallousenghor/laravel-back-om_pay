<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;
use Carbon\Carbon;

class Authentification extends Model
{
    use HasFactory;

    protected $table = 'authentifications';

    protected $fillable = [
        'id_utilisateur',
        'jeton_acces',
        'jeton_rafraichissement',
        'date_expiration',
    ];

    protected $hidden = [
        'jeton_acces',
        'jeton_rafraichissement',
    ];

    protected $casts = [
        'date_expiration' => 'datetime',
    ];

    // Relationships
    public function utilisateur(): BelongsTo
    {
        return $this->belongsTo(Utilisateur::class, 'id_utilisateur');
    }

    // Scopes
    public function scopeValides($query)
    {
        return $query->where('date_expiration', '>', now());
    }

    public function scopeExpires($query)
    {
        return $query->where('date_expiration', '<=', now());
    }

    // Methods
    public static function genererJetons(int $utilisateurId): array
    {
        $jetonAcces = Str::random(64);
        $jetonRafraichissement = Str::random(64);
        $dateExpiration = now()->addHours(2); // 2 heures pour le jeton d'accès

        return [
            'jeton_acces' => $jetonAcces,
            'jeton_rafraichissement' => $jetonRafraichissement,
            'date_expiration' => $dateExpiration,
        ];
    }

    public function rafraichirJeton(): ?array
    {
        if ($this->date_expiration <= now()) {
            return null; // Jeton expiré
        }

        $nouveauxJetons = self::genererJetons($this->id_utilisateur);

        $this->update([
            'jeton_acces' => $nouveauxJetons['jeton_acces'],
            'jeton_rafraichissement' => $nouveauxJetons['jeton_rafraichissement'],
            'date_expiration' => $nouveauxJetons['date_expiration'],
        ]);

        return $nouveauxJetons;
    }

    public static function validerJeton(string $jeton): ?self
    {
        return self::where('jeton_acces', $jeton)
            ->where('date_expiration', '>', now())
            ->first();
    }

    public function revoquerJeton(): bool
    {
        return $this->update(['date_expiration' => now()]);
    }

    public function estExpire(): bool
    {
        return $this->date_expiration <= now();
    }
}
