<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Pour PostgreSQL, on doit utiliser CASCADE
        $tables = [
            'contacts',
            'paiements',
            'qr_codes',
            'code_paiements',
            'marchands',
            'transferts',
            'destinataires',
            'transactions',
            'portefeuilles',
            'authentifications',
            'parametres_securites'
        ];

        foreach ($tables as $table) {
            DB::statement('DROP TABLE IF EXISTS ' . $table . ' CASCADE');
        }
    }

    public function down()
    {
        // Pas de restauration nécessaire
    }
};