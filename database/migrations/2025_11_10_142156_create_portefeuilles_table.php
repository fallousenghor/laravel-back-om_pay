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
        Schema::create('portefeuilles', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('id_utilisateur');
            $table->decimal('solde', 15, 2)->default(0);
            $table->string('devise', 3)->default('XOF');
            $table->timestamp('derniere_mise_a_jour')->nullable();
            $table->timestamps();

            $table->foreign('id_utilisateur')->references('id')->on('utilisateurs')->onDelete('cascade');
            $table->index(['id_utilisateur', 'solde'], 'portefeuilles_utilisateur_solde_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('portefeuilles');
    }
};
