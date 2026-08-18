<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('cid10_categorias')) {
            return;
        }

        Schema::create('cid10_categorias', function (Blueprint $table): void {
            $table->id();
            $table->boolean('ativo')->default(true);
            $table->timestamps();
            $table->softDeletes();
            $table->char('cat', 3)->nullable();
            $table->string('classif')->nullable();
            $table->string('descricao')->nullable();
            $table->string('descrabrev')->nullable();
            $table->string('refer')->nullable();
            $table->string('excluidos')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cid10_categorias');
    }
};
