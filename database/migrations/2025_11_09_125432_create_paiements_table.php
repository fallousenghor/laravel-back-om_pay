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
        Schema::create('paiements', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('id_transaction')->constrained('transactions')->onDelete('cascade');
            $table->foreignUuid('id_marchand')->constrained('marchands');
            $table->enum('mode_paiement', ['qr_code', 'code_numerique']);
            $table->json('details_paiement')->nullable();
            $table->foreignUuid('id_qr_code')->nullable()->constrained('qr_codes')->onDelete('set null');
            $table->foreignUuid('id_code_paiement')->nullable()->constrained('code_paiements')->onDelete('set null');
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
