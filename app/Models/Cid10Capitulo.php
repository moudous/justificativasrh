<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Cid10Capitulo extends Model
{
    use SoftDeletes;

    protected $table = 'cid10_capitulos';

    protected $fillable = ['ativo', 'numcap', 'catinic', 'catfim', 'descricao', 'descrabrev'];

    protected $casts = [
        'ativo' => 'boolean',
        'numcap' => 'integer',
    ];
}
