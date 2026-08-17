<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Setor extends Model
{
    use SoftDeletes;

    protected $table = 'setores';

    protected $fillable = ['unidade_id', 'nome', 'ativo'];

    protected $casts = ['ativo' => 'boolean', 'unidade_id' => 'integer'];

    public function unidade(): BelongsTo
    {
        return $this->belongsTo(Unidade::class)->withTrashed();
    }
}
