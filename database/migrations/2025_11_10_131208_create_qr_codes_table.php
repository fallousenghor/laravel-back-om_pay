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
        Schema::create('qr_codes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('id_marchand')->nullable();
            $table->uuid('id_utilisateur')->nullable();
            $table->json('donnees');
            $table->decimal('montant', 15, 2)->nullable();
            $table->timestamp('date_generation');
            $table->timestamp('date_expiration');
            $table->boolean('utilise')->default(false);
            $table->timestamps();

            $table->index(['id_utilisateur', 'utilise']);
            $table->index(['date_expiration']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('qr_codes');
    }
};
