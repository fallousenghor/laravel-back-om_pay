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
        Schema::table('orange_money', function (Blueprint $table) {
            // Code marchand pour certains comptes OrangeMoney
            $table->string('code', 100)->nullable()->after('status')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orange_money', function (Blueprint $table) {
            $table->dropIndex(['code']);
            $table->dropColumn('code');
        });
    }
};
