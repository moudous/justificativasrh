<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('justificativas', function (Blueprint $table): void {
            $table->dropColumn('nome');
            $table->string('anexo_caminho')->nullable()->after('status');
            $table->string('anexo_nome_original')->nullable()->after('anexo_caminho');
            $table->string('anexo_mime', 100)->nullable()->after('anexo_nome_original');
            $table->boolean('atestado_medico')->default(false)->after('anexo_mime');
            $table->string('tipo_atestado', 30)->nullable()->after('atestado_medico');
            $table->string('grau_parentesco', 30)->nullable()->after('tipo_atestado');
        });
    }

    public function down(): void
    {
        Schema::table('justificativas', function (Blueprint $table): void {
            $table->string('nome')->nullable()->after('id');
            $table->dropColumn([
                'anexo_caminho',
                'anexo_nome_original',
                'anexo_mime',
                'atestado_medico',
                'tipo_atestado',
                'grau_parentesco',
            ]);
        });
    }
};
