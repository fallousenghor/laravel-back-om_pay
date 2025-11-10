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
        // This migration was intentionally left empty because merchant codes are stored
        // directly on the `orange_money` table (nullable `code` column). Keeping this
        // file to avoid altering migration order, but it performs no action.
        return;
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No-op
        return;
    }
};
