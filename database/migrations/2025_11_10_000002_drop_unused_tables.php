<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Supprimer d'abord les tables qui ont des clés étrangères
        Schema::dropIfExists('paiements');
        Schema::dropIfExists('code_paiements');
        Schema::dropIfExists('qr_codes');
        Schema::dropIfExists('contacts');
        Schema::dropIfExists('transferts');
        Schema::dropIfExists('transactions');
        Schema::dropIfExists('destinataires');
        Schema::dropIfExists('portefeuilles');
        Schema::dropIfExists('authentifications');
        Schema::dropIfExists('parametres_securites');

        // Supprimer ensuite les tables principales
        Schema::dropIfExists('marchands');
    }

    public function down()
    {
        // La restauration des tables n'est pas nécessaire dans ce cas
    }
};