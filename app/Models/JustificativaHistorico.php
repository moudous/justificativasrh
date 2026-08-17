<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JustificativaHistorico extends Model
{
    protected $table = 'justificativa_historicos';

    protected $fillable = ['evento', 'status_anterior', 'status_novo'];

    public function justificativa(): BelongsTo
    {
        return $this->belongsTo(Justificativa::class);
    }
}
