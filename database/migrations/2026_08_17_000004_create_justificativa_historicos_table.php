<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('justificativa_historicos', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('justificativa_id')->constrained('justificativas')->cascadeOnDelete();
            $table->string('evento', 50);
            $table->string('status_anterior', 50)->nullable();
            $table->string('status_novo', 50)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('justificativa_historicos');
    }
};
