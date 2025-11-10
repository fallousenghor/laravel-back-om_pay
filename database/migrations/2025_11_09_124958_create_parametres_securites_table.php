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
        Schema::create('parametres_securites', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('id_utilisateur')->constrained('utilisateurs')->onDelete('cascade');
            $table->boolean('biometrie_active')->default(false);
            $table->integer('tentatives_echouees')->default(0);
            $table->timestamp('date_deblocage')->nullable();
            $table->timestamps();

            $table->index(['id_utilisateur', 'tentatives_echouees']);
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
