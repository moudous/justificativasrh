<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Colaborador extends Model
{
    protected $table = 'colaboradores';

    public $incrementing = false;

    protected $keyType = 'int';

    protected $fillable = ['id', 'nome', 'email', 'perfil', 'perfil_id', 'ativo', 'setor_id', 'responsavel_id'];

    protected $casts = [
        'ativo' => 'boolean',
        'perfil_id' => 'integer',
        'setor_id' => 'integer',
        'responsavel_id' => 'integer',
    ];

    public function justificativas(): HasMany
    {
        return $this->hasMany(Justificativa::class);
    }

    public function responsavel(): BelongsTo
    {
        return $this->belongsTo(Responsavel::class);
    }

    public function setor(): BelongsTo
    {
        return $this->belongsTo(Setor::class)->withTrashed();
    }
}
