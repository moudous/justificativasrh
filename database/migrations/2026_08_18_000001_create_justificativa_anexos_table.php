<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('justificativa_anexos')) {
            return;
        }

        Schema::create('justificativa_anexos', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('justificativa_id')->constrained('justificativas')->cascadeOnDelete();
            $table->string('caminho');
            $table->string('nome_original');
            $table->string('mime', 100);
            $table->timestamps();
        });

        DB::table('justificativas')
            ->whereNotNull('anexo_caminho')
            ->orderBy('id')
            ->each(function (object $justificativa): void {
                DB::table('justificativa_anexos')->insert([
                    'justificativa_id' => $justificativa->id,
                    'caminho' => $justificativa->anexo_caminho,
                    'nome_original' => $justificativa->anexo_nome_original ?? basename($justificativa->anexo_caminho),
                    'mime' => $justificativa->anexo_mime ?? 'application/octet-stream',
                    'created_at' => $justificativa->created_at,
                    'updated_at' => $justificativa->updated_at,
                ]);
            });
    }

    public function down(): void
    {
        Schema::dropIfExists('justificativa_anexos');
    }
};
