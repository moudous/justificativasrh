<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Cid10Subcategoria extends Model
{
    use SoftDeletes;

    protected $table = 'cid10_subcategorias';
    protected $fillable = ['ativo', 'subcat', 'classif', 'restrsexo', 'causaobito', 'descricao', 'descrabrev', 'refer', 'excluidos'];
    protected $casts = ['ativo' => 'boolean'];
}
