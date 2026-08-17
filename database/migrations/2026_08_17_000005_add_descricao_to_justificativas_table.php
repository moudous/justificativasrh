<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('justificativas', function (Blueprint $table): void {
            $table->text('descricao')->nullable()->after('nome');
        });
    }

    public function down(): void
    {
        Schema::table('justificativas', function (Blueprint $table): void {
            $table->dropColumn('descricao');
        });
    }
};
