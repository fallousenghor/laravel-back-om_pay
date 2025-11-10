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
        Schema::create('utilisateurs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('numero_telephone', 20)->unique();
            $table->string('prenom', 100);
            $table->string('nom', 100);
            $table->string('email', 255)->unique()->nullable();
            $table->string('code_pin', 255)->nullable(); // Hashed - nullable for temporary inscription
            $table->string('numero_cni', 50)->unique()->nullable();
            $table->enum('statut_kyc', ['non_verifie', 'en_cours', 'verifie', 'rejete', 'en_attente_verification'])->default('non_verifie');
            $table->boolean('biometrie_activee')->default(false);
            $table->string('otp', 10)->nullable(); // OTP code
            $table->timestamp('otp_expires_at')->nullable(); // OTP expiration
            $table->string('jeton_biometrique', 255)->nullable(); // Biometric token
            $table->timestamp('date_creation')->useCurrent();
            $table->timestamp('derniere_connexion')->nullable();
            $table->timestamps();

            $table->index(['numero_telephone', 'statut_kyc'], 'utilisateurs_numero_statut_index');
            $table->index('email', 'utilisateurs_email_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('utilisateurs');
    }
};
