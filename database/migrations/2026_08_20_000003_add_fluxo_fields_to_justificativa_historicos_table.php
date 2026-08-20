<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('justificativa_historicos', function (Blueprint $table): void {
            $table->string('etapa_controle', 20)->nullable()->after('evento');
            $table->string('historico', 100)->nullable()->after('etapa_controle');
            $table->string('mensagem_rh', 100)->nullable()->after('historico');
        });

        DB::table('justificativa_historicos')->where('evento', 'criada')->update([
            'etapa_controle' => 'colaborador',
            'historico' => 'Justificativa criada pelo colaborador',
        ]);
        DB::table('justificativa_historicos')->where('evento', 'alterada')->update([
            'historico' => 'Justificativa editada',
        ]);
    }

    public function down(): void
    {
        Schema::table('justificativa_historicos', function (Blueprint $table): void {
            $table->dropColumn(['etapa_controle', 'historico', 'mensagem_rh']);
        });
    }
};
