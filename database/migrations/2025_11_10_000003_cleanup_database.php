<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Tables à conserver
        $keep_tables = [
            'migrations',
            'users',
            'utilisateurs',
            'orange_money',
            'failed_jobs',
            'password_reset_tokens',
            'personal_access_tokens'
        ];

        // Récupérer toutes les tables
        $tables = DB::select('SELECT table_name FROM information_schema.tables WHERE table_schema = current_schema()');

        // Supprimer toutes les tables sauf celles à conserver
        foreach ($tables as $table) {
            $table_name = $table->table_name;
            if (!in_array($table_name, $keep_tables)) {
                Schema::drop($table_name);
            }
        }
    }

    public function down()
    {
        // Pas de restauration nécessaire
    }
};