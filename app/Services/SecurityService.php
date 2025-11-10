<?php

namespace App\Services;

use Illuminate\Support\Facades\Hash;

class SecurityService
{
    // Changer le code PIN
    public function changerPin($utilisateur, $ancienPin, $nouveauPin)
    {
        if (!Hash::check($ancienPin, $utilisateur->code_pin)) {
            return [
                'success' => false,
                'error' => [
                    'code' => 'USER_006',
                    'message' => 'Ancien PIN incorrect'
                ],
                'status' => 401
            ];
        }

        if (Hash::check($nouveauPin, $utilisateur->code_pin)) {
            return [
                'success' => false,
                'error' => [
                    'code' => 'USER_007',
                    'message' => 'Nouveau PIN identique à l\'ancien'
                ],
                'status' => 422
            ];
        }

        $utilisateur->update(['code_pin' => Hash::make($nouveauPin)]);

        return [
            'success' => true,
            'message' => 'Code PIN modifié avec succès'
        ];
    }

    // Activer la biométrie
    public function activerBiometrie($utilisateur, $codePin, $jetonBiometrique)
    {
        if (!Hash::check($codePin, $utilisateur->code_pin)) {
            return [
                'success' => false,
                'error' => [
                    'code' => 'USER_006',
                    'message' => 'PIN incorrect'
                ],
                'status' => 401
            ];
        }

        $utilisateur->update([
            'biometrie_activee' => true,
            'jeton_biometrique' => $jetonBiometrique,
        ]);

        return [
            'success' => true,
            'data' => [
                'biometrieActivee' => true,
            ],
            'message' => 'Biométrie activée avec succès'
        ];
    }

    // Vérifier le PIN
    public function verifierPin($utilisateur, $codePin)
    {
        return Hash::check($codePin, $utilisateur->code_pin);
    }
}