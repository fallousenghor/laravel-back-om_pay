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
        Schema::create('transferts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('id_transaction')->constrained('transactions')->onDelete('cascade');
            $table->foreignUuid('id_expediteur')->constrained('utilisateurs');
            $table->string('numero_telephone_destinataire', 20);
            $table->string('nom_destinataire', 100);
            $table->text('note')->nullable();
            $table->timestamp('date_expiration')->nullable();
            $table->timestamps();

            $table->index(['id_expediteur', 'numero_telephone_destinataire']);
            $table->index('date_expiration');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transferts');
    }
};
