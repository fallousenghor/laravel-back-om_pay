<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public $withinTransaction = false;
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('id_portefeuille')->constrained('portefeuilles')->onDelete('cascade');
            $table->enum('type', ['transfert', 'paiement']);
            $table->decimal('montant', 15, 2);
            $table->string('devise', 3)->default('XOF');
            $table->enum('statut', ['en_attente', 'en_cours', 'reussie', 'echouee', 'annulee'])->default('en_attente');
            $table->decimal('frais', 10, 2)->default(0);
            $table->string('reference', 50)->unique();
            $table->timestamp('date_transaction')->useCurrent();
            $table->timestamps();

            $table->index(['id_portefeuille', 'statut', 'date_transaction'], 'transactions_portefeuille_statut_date_index');
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
