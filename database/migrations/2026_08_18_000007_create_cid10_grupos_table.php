<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('cid10_grupos')) {
            return;
        }

        Schema::create('cid10_grupos', function (Blueprint $table): void {
            $table->id();
            $table->text('titulo');
            $table->boolean('ativo')->default(true);
            $table->timestamps();
            $table->softDeletes();
            $table->integer('cod_grupo')->nullable();
            $table->string('catinic')->nullable();
            $table->string('catfim')->nullable();
            $table->string('descricao')->nullable();
            $table->string('descrabrev')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cid10_grupos');
    }
};
