<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::dropIfExists('orange_money');
        
        Schema::create('orange_money', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('numero_telephone')->unique();
            $table->string('nom');
            $table->string('prenom');
            $table->string('numero_cni')->unique();
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