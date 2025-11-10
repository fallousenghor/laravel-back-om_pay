<?php

namespace App\Services;

use App\Models\Authentification;
use Illuminate\Support\Str;
use Carbon\Carbon;

class TokenService
{
    // Générer des tokens pour un utilisateur
    public function generateTokens($utilisateur)
    {
        $accessToken = Str::random(64);
        $refreshToken = Str::random(64);

        Authentification::create([
            'id_utilisateur' => $utilisateur->getKey(),
            'jeton_acces' => $accessToken,
            'jeton_rafraichissement' => $refreshToken,
            'date_expiration' => Carbon::now()->addHours(24),
        ]);

        return [
            'accessToken' => $accessToken,
            'refreshToken' => $refreshToken,
        ];
    }

    // Rafraîchir les tokens
    public function refreshTokens($jetonRafraichissement)
    {
        $auth = Authentification::where('jeton_rafraichissement', $jetonRafraichissement)->first();

        if (!$auth || ($auth->date_expiration && Carbon::now()->isAfter($auth->date_expiration))) {
            return [
                'success' => false,
                'error' => [
                    'code' => 'AUTH_006',
                    'message' => 'Token invalide ou expiré'
                ],
                'status' => 401
            ];
        }

        $newAccessToken = Str::random(64);
        $newRefreshToken = Str::random(64);

        $auth->update([
            'jeton_acces' => $newAccessToken,
            'jeton_rafraichissement' => $newRefreshToken,
            'date_expiration' => Carbon::now()->addHours(24),
        ]);

        return [
            'success' => true,
            'data' => [
                'accessToken' => $newAccessToken,
                'refreshToken' => $newRefreshToken,
            ]
        ];
    }

    // Invalider un token
    public function invalidateToken($token)
    {
        $auth = Authentification::where('jeton_acces', $token)->first();

        if ($auth) {
            $auth->delete();
        }

        return true;
    }
}