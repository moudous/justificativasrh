<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::rename('justificativa_anexos', 'anexos_justificativas');
    }

    public function down(): void
    {
        Schema::rename('anexos_justificativas', 'justificativa_anexos');
    }
};
