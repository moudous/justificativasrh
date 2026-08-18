<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Responsavel extends Model
{
    use SoftDeletes;

    protected $table = 'responsaveis';
    protected $fillable = ['nome', 'cargo', 'colaborador_id'];
    protected $casts = ['colaborador_id' => 'integer'];

    public function colaborador(): BelongsTo
    {
        return $this->belongsTo(Colaborador::class);
    }

    public function setores(): BelongsToMany
    {
        return $this->belongsToMany(Setor::class, 'responsavel_setor');
    }
}
