<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('justificativas')
            ->where('status', 'Pendente')
            ->update(['status' => 'Ainda não enviado ao responsável']);

        DB::table('justificativa_historicos')->where('status_anterior', 'Pendente')
            ->update(['status_anterior' => 'Ainda não enviado ao responsável']);
        DB::table('justificativa_historicos')->where('status_novo', 'Pendente')
            ->update(['status_novo' => 'Ainda não enviado ao responsável']);
    }

    public function down(): void
    {
        DB::table('justificativas')
            ->where('status', 'Ainda não enviado ao responsável')
            ->update(['status' => 'Pendente']);

        DB::table('justificativa_historicos')->where('status_anterior', 'Ainda não enviado ao responsável')
            ->update(['status_anterior' => 'Pendente']);
        DB::table('justificativa_historicos')->where('status_novo', 'Ainda não enviado ao responsável')
            ->update(['status_novo' => 'Pendente']);
    }
};
