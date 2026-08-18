<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Cid10Grupo extends Model
{
    use SoftDeletes;

    protected $table = 'cid10_grupos';
    protected $fillable = ['titulo', 'ativo', 'cod_grupo', 'catinic', 'catfim', 'descricao', 'descrabrev'];
    protected $casts = ['ativo' => 'boolean', 'cod_grupo' => 'integer'];
}
