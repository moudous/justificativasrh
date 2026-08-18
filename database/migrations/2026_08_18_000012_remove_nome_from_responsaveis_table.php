<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('responsaveis', function (Blueprint $table): void {
            $table->dropColumn('nome');
        });
    }

    public function down(): void
    {
        Schema::table('responsaveis', function (Blueprint $table): void {
            $table->string('nome')->nullable()->after('id');
        });
    }
};