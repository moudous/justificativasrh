<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('colaboradores', function (Blueprint $table): void {
            $table->foreignId('setor_id')->nullable()->after('perfil_id')->constrained('setores')->nullOnDelete();
            $table->foreignId('responsavel_id')->nullable()->after('setor_id')->constrained('responsaveis')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('colaboradores', function (Blueprint $table): void {
            $table->dropForeign(['responsavel_id']);
            $table->dropForeign(['setor_id']);
            $table->dropColumn(['responsavel_id', 'setor_id']);
        });
    }
};