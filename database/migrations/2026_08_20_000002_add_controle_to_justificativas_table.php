<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('justificativas', function (Blueprint $table): void {
            $table->string('controle', 20)->default('colaborador')->after('status')->index();
            $table->string('mensagem_rh', 100)->nullable()->after('controle');
        });
    }

    public function down(): void
    {
        Schema::table('justificativas', function (Blueprint $table): void {
            $table->dropIndex(['controle']);
            $table->dropColumn(['controle', 'mensagem_rh']);
        });
    }
};
