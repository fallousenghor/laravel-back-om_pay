<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('id_utilisateur');
            $table->enum('type', ['transfert', 'paiement']);
            $table->decimal('montant', 15, 2);
            $table->string('devise', 3)->default('XOF');
            $table->enum('statut', ['en_attente', 'en_cours', 'termine', 'echouee', 'annulee'])->default('en_attente');
            $table->decimal('frais', 10, 2)->default(0);
            $table->string('reference', 50)->unique();
            $table->timestamp('date_transaction')->useCurrent();

            // Champs spécifiques aux transferts
            $table->string('numero_telephone_destinataire')->nullable();
            $table->string('nom_destinataire')->nullable();

            // Champs spécifiques aux paiements
            $table->string('nom_marchand')->nullable();
            $table->string('categorie_marchand')->nullable();

            $table->text('note')->nullable();
            $table->timestamps();

            $table->foreign('id_utilisateur')->references('id')->on('utilisateurs')->onDelete('cascade');
            $table->index(['id_utilisateur', 'statut', 'date_transaction'], 'transactions_utilisateur_statut_date_index');
            $table->index(['type', 'statut'], 'transactions_type_statut_index');
            $table->index('reference', 'transactions_reference_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
