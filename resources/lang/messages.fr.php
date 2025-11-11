<?php

return [
    // Authentification
    'auth' => [
        'initiate_registration' => [
            'success' => 'Code de vérification envoyé avec succès',
            'phone_invalid' => 'Numéro de téléphone invalide',
        ],
        'verify_otp' => [
            'success' => 'Code OTP vérifié avec succès',
            'invalid' => 'Code OTP invalide',
            'expired' => 'Code OTP expiré',
        ],
        'login' => [
            'success' => 'Connexion réussie',
            'invalid_credentials' => 'Numéro ou PIN incorrect',
        ],
        'logout' => [
            'success' => 'Déconnexion réussie',
        ],
    ],

    // Paiements
    'paiement' => [
        'scanner_qr' => [
            'success' => 'QR code scanné avec succès',
            'invalid' => 'QR code invalide',
        ],
        'saisir_code' => [
            'success' => 'Code de paiement validé',
            'invalid' => 'Code de paiement invalide',
        ],
        'confirmer' => [
            'success' => 'Paiement confirmé et exécuté',
            'invalid_pin' => 'PIN incorrect',
            'already_processed' => 'Paiement déjà traité',
        ],
        'annuler' => [
            'success' => 'Paiement annulé',
            'cannot_cancel' => 'Paiement ne peut pas être annulé',
            'already_processed' => 'Paiement déjà traité',
        ],
    ],

    // Portefeuille
    'portefeuille' => [
        'solde' => [
            'success' => 'Solde récupéré avec succès',
            'unauthorized' => 'Non authentifié',
            'invalid_token' => 'Token invalide',
        ],
        'historique' => [
            'success' => 'Historique récupéré avec succès',
        ],
        'details_transaction' => [
            'success' => 'Détails de la transaction récupérés',
            'not_found' => 'Transaction non trouvée',
        ],
    ],

    // Transferts
    'transfert' => [
        'initier' => [
            'success' => 'Transfert initié',
            'validation_error' => 'Erreur de validation ou solde insuffisant',
        ],
        'confirmer' => [
            'success' => 'Transfert confirmé et exécuté',
            'invalid_pin' => 'PIN incorrect',
            'already_processed' => 'Transfert déjà traité',
        ],
        'annuler' => [
            'success' => 'Transfert annulé',
            'cannot_cancel' => 'Transfert ne peut pas être annulé',
            'already_processed' => 'Transfert déjà traité',
        ],
    ],

    // Erreurs générales
    'errors' => [
        'validation' => 'Erreur de validation',
        'unauthorized' => 'Non autorisé',
        'not_found' => 'Ressource non trouvée',
        'server_error' => 'Erreur serveur',
        'qr_invalid' => 'QR code invalide',
        'code_invalid' => 'Code de paiement invalide',
        'pin_incorrect' => 'PIN incorrect',
        'already_processed' => 'Paiement déjà traité',
        'cannot_cancel' => 'Paiement ne peut pas être annulé',
    ],

    // Messages de succès généraux
    'success' => [
        'created' => 'Créé avec succès',
        'updated' => 'Mis à jour avec succès',
        'deleted' => 'Supprimé avec succès',
    ],

    // Messages SMS
    'sms' => [
        'otp_verification' => 'Votre code de vérification Om-Pay est : :code. Il expire dans 15 minutes.',
        'test_sms' => 'Test SMS Om-Pay at :date',
        'sms_sent_success' => 'SMS envoyé avec succès à :phone',
    ],

    // Messages des seeders
    'seeders' => [
        'test_insertion_success' => 'Test insertion réussie',
        'creating_recipients' => 'Créer des destinataires de test',
        'creating_additional_recipients' => 'Créer des destinataires supplémentaires',
        'creating_merchants' => 'Créer des marchands de test',
        'creating_additional_merchants' => 'Créer des marchands supplémentaires',
    ],
];