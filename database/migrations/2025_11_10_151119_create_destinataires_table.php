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
        Schema::create('destinataires', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('numero_telephone', 20)->unique();
            $table->string('nom', 100);
            $table->enum('operateur', ['orange', 'free', 'expresso', 'autre'])->default('orange');
            $table->boolean('est_valide')->default(true);
            $table->timestamps();

            $table->index(['numero_telephone', 'est_valide']);
            $table->index('operateur');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('destinataires');
    }
};
