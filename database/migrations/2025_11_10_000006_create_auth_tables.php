<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Table pour stocker les codes OTP envoyés
        Schema::create('verification_codes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('numero_telephone');
            $table->string('code');
            $table->string('token')->unique(); // Pour le lien unique
            $table->timestamp('expire_at');
            $table->boolean('used')->default(false);
            $table->timestamps();
        });

        // Table pour les sessions actives
        Schema::create('sessions_ompay', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('utilisateur_id');
            $table->string('token')->unique();
            $table->timestamp('last_activity');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('verification_codes');
        Schema::dropIfExists('sessions_ompay');
    }
};