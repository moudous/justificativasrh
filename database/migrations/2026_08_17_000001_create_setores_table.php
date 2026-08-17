<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('setores', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('unidade_id')->constrained('unidades')->restrictOnDelete();
            $table->string('nome');
            $table->boolean('ativo')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('setores');
    }
};
