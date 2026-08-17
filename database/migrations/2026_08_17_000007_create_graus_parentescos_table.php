<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('graus_parentescos', function (Blueprint $table): void {
            $table->id();
            $table->string('nome');
            $table->boolean('ativo')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table('justificativas', function (Blueprint $table): void {
            $table->dropColumn('grau_parentesco');
            $table->foreignId('grau_parentesco_id')->nullable()->after('tipo_atestado')
                ->constrained('graus_parentescos')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('justificativas', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('grau_parentesco_id');
            $table->string('grau_parentesco', 30)->nullable()->after('tipo_atestado');
        });

        Schema::dropIfExists('graus_parentescos');
    }
};
