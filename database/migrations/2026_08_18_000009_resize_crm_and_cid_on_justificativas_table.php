<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('justificativas', function (Blueprint $table): void {
            $table->string('crm_medico', 15)->nullable()->change();
            $table->string('cid', 7)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('justificativas', function (Blueprint $table): void {
            $table->string('crm_medico')->nullable()->change();
            $table->string('cid')->nullable()->change();
        });
    }
};
