<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class JustificativaAnexo extends Model
{
    protected $table = 'anexos_justificativas';

    protected $fillable = ['caminho', 'nome_original', 'mime'];

    protected static function booted(): void
    {
        static::deleted(function (JustificativaAnexo $anexo): void {
            Storage::disk('local')->delete($anexo->caminho);
        });
    }

    public function justificativa(): BelongsTo
    {
        return $this->belongsTo(Justificativa::class);
    }
}
