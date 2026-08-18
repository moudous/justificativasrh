<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('cid10_subcategorias')) {
            return;
        }

        Schema::create('cid10_subcategorias', function (Blueprint $table): void {
            $table->id();
            $table->boolean('ativo')->default(true);
            $table->string('subcat', 4)->nullable();
            $table->string('classif')->nullable();
            $table->string('restrsexo')->nullable();
            $table->string('causaobito')->nullable();
            $table->string('descricao', 262)->nullable();
            $table->string('descrabrev')->nullable();
            $table->string('refer')->nullable();
            $table->string('excluidos')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cid10_subcategorias');
    }
};
