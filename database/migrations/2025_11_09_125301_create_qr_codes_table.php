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
        Schema::create('qr_codes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('id_marchand')->nullable()->constrained('marchands')->onDelete('cascade');
            $table->foreignUuid('id_utilisateur')->nullable()->constrained('utilisateurs')->onDelete('cascade');
            $table->text('donnees');
            $table->decimal('montant', 15, 2)->nullable();
            $table->timestamp('date_generation')->useCurrent();
            $table->timestamp('date_expiration');
            $table->boolean('utilise')->default(false);
            $table->timestamps();

            $table->index(['id_marchand', 'utilise', 'date_expiration']);
            $table->index(['id_utilisateur', 'utilise', 'date_expiration']);
            $table->index('date_generation');
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
