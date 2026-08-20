<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('justificativas', function (Blueprint $table): void {
            $table->string('tipo_ocorrencia', 20)->default('data')->after('categoria_id');
            $table->date('data_ocorrencia')->nullable()->after('tipo_ocorrencia');
            $table->time('hora_inicial')->nullable()->after('data_ocorrencia');
            $table->time('hora_final')->nullable()->after('hora_inicial');
            $table->date('data_inicial')->nullable()->after('hora_final');
            $table->unsignedInteger('numero_dias')->nullable()->after('data_inicial');
            $table->date('data_retorno')->nullable()->after('numero_dias');
        });
    }

    public function down(): void
    {
        Schema::table('justificativas', function (Blueprint $table): void {
            $table->dropColumn([
                'tipo_ocorrencia',
                'data_ocorrencia',
                'hora_inicial',
                'hora_final',
                'data_inicial',
                'numero_dias',
                'data_retorno',
            ]);
        });
    }
};
