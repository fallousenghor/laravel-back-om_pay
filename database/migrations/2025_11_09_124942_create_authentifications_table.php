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
        Schema::create('authentifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('id_utilisateur')->constrained('utilisateurs')->onDelete('cascade');
            $table->string('jeton_acces', 500);
            $table->string('jeton_rafraichissement', 500);
            $table->timestamp('date_expiration');
            $table->timestamps();

            $table->index(['id_utilisateur', 'date_expiration']);
            $table->unique(['jeton_acces']);
            $table->unique(['jeton_rafraichissement']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('authentifications');
    }
};
