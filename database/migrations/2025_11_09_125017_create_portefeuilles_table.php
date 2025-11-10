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
        Schema::create('portefeuilles', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('id_utilisateur')->constrained('utilisateurs')->onDelete('cascade');
            $table->decimal('solde', 15, 2)->default(0);
            $table->string('devise', 3)->default('XOF');
            $table->timestamp('derniere_mise_a_jour')->useCurrent();
            $table->timestamps();

            $table->index(['id_utilisateur', 'solde']);
            $table->unique(['id_utilisateur', 'devise']);
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
