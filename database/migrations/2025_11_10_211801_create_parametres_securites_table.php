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
        Schema::create('parametres_securites', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('id_utilisateur');
            $table->boolean('biometrie_active')->default(false);
            $table->integer('tentatives_echouees')->default(0);
            $table->timestamps();

            $table->foreign('id_utilisateur')
                  ->references('id')
                  ->on('utilisateurs')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('parametres_securites');
    }
};
