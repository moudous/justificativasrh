<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Colaborador extends Model
{
    protected $table = 'colaboradores';

    public $incrementing = false;

    protected $keyType = 'int';

    protected $fillable = ['id', 'nome', 'email', 'perfil', 'perfil_id', 'ativo'];

    protected $casts = [
        'ativo' => 'boolean',
        'perfil_id' => 'integer',
    ];

    public function justificativas(): HasMany
    {
        return $this->hasMany(Justificativa::class);
    }

    public function responsavel(): HasOne
    {
        return $this->hasOne(Responsavel::class);
    }
}
