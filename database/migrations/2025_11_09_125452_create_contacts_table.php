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
        Schema::create('contacts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('id_utilisateur')->constrained('utilisateurs')->onDelete('cascade');
            $table->string('nom', 100);
            $table->string('numero_telephone', 20);
            $table->string('photo', 255)->nullable();
            $table->integer('nombre_transactions')->default(0);
            $table->timestamp('derniere_transaction')->nullable();
            $table->timestamps();

            $table->index(['id_utilisateur', 'numero_telephone']);
            $table->index(['id_utilisateur', 'nombre_transactions']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contacts');
    }
};

