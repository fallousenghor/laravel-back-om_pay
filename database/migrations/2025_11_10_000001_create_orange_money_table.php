<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('orange_money', function (Blueprint $table) {
            $table->id();
            $table->string('numero_telephone')->unique();
            $table->string('nom');
            $table->string('prenom');
            $table->decimal('solde', 15, 2)->default(0);
            $table->string('status')->default('active');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('orange_money');
    }
};