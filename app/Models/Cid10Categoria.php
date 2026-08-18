<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Cid10Categoria extends Model
{
    use SoftDeletes;

    protected $table = 'cid10_categorias';
    protected $fillable = ['ativo', 'cat', 'classif', 'descricao', 'descrabrev', 'refer', 'excluidos'];
    protected $casts = ['ativo' => 'boolean'];
}
