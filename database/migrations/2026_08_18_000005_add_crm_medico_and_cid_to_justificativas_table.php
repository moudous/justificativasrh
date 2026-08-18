<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('justificativas', function (Blueprint $table): void {
            $table->string('crm_medico')->nullable()->after('atestado_medico');
            $table->string('cid')->nullable()->after('crm_medico');
        });
    }

    public function down(): void
    {
        Schema::table('justificativas', function (Blueprint $table): void {
            $table->dropColumn(['crm_medico', 'cid']);
        });
    }
};
