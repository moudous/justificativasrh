<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('responsaveis', function (Blueprint $table): void {
            $table->id();
            $table->string('nome');
            $table->string('cargo');
            $table->unsignedBigInteger('colaborador_id')->unique();
            $table->foreign('colaborador_id')->references('id')->on('colaboradores')->restrictOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('responsavel_setor', function (Blueprint $table): void {
            $table->foreignId('responsavel_id')->constrained('responsaveis')->cascadeOnDelete();
            $table->foreignId('setor_id')->constrained('setores')->restrictOnDelete();
            $table->primary(['responsavel_id', 'setor_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('responsavel_setor');
        Schema::dropIfExists('responsaveis');
    }
};
