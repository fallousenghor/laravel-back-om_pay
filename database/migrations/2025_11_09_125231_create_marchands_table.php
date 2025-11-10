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
        Schema::create('marchands', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('nom', 100);
            $table->string('numero_telephone', 20)->unique();
            $table->string('adresse', 255)->nullable();
            $table->string('logo', 255)->nullable();
            $table->boolean('actif')->default(true);
            $table->boolean('accepte_qr')->default(true);
            $table->boolean('accepte_code')->default(true);
            $table->timestamps();

            $table->index(['numero_telephone', 'actif']);
            $table->index(['accepte_qr', 'accepte_code']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('marchands');
    }
};
