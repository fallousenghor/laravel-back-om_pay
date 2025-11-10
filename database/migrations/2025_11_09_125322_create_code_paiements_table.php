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
        Schema::create('code_paiements', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('code', 10)->unique();
            $table->foreignUuid('id_marchand')->constrained('marchands')->onDelete('cascade');
            $table->decimal('montant', 15, 2);
            $table->timestamp('date_generation')->useCurrent();
            $table->timestamp('date_expiration');
            $table->boolean('utilise')->default(false);
            $table->timestamps();

            $table->index(['code', 'utilise']);
            $table->index(['id_marchand', 'utilise', 'date_expiration']);
            $table->index('date_generation');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('code_paiements');
    }
};
