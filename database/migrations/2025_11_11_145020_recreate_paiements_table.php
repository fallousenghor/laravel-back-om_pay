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
        Schema::create('paiements', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('id_transaction')->constrained('transactions')->onDelete('cascade');
            $table->uuid('id_marchand')->nullable();
            $table->enum('mode_paiement', ['qr_code', 'code_numerique', 'code_marchand']);
            $table->json('details_paiement')->nullable();
            $table->uuid('id_qr_code')->nullable();
            $table->uuid('id_code_paiement')->nullable();
            $table->timestamps();

            $table->index(['id_marchand', 'mode_paiement']);
            $table->index('id_qr_code');
            $table->index('id_code_paiement');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('paiements');
    }
};
