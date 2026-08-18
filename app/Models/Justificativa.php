<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Justificativa extends Model
{
    use SoftDeletes;

    protected $table = 'justificativas';

    protected $fillable = [
        'descricao',
        'colaborador_id',
        'categoria_id',
        'status',
        'anexo_caminho',
        'anexo_nome_original',
        'anexo_mime',
        'atestado_medico',
        'crm_medico',
        'cid',
        'tipo_atestado',
        'grau_parentesco_id',
    ];

    protected $casts = [
        'colaborador_id' => 'integer',
        'categoria_id' => 'integer',
        'atestado_medico' => 'boolean',
        'grau_parentesco_id' => 'integer',
    ];

    protected static function booted(): void
    {
        static::created(function (Justificativa $justificativa): void {
            $justificativa->historicos()->create([
                'evento' => 'criada',
                'status_novo' => $justificativa->status,
            ]);
        });

        static::updated(function (Justificativa $justificativa): void {
            $statusAlterado = $justificativa->wasChanged('status');

            $justificativa->historicos()->create([
                'evento' => $statusAlterado ? 'status_alterado' : 'alterada',
                'status_anterior' => $statusAlterado ? $justificativa->getOriginal('status') : null,
                'status_novo' => $statusAlterado ? $justificativa->status : null,
            ]);
        });

        static::forceDeleting(function (Justificativa $justificativa): void {
            $justificativa->anexos()->get()->each->delete();
        });
    }

    public function colaborador(): BelongsTo
    {
        return $this->belongsTo(Colaborador::class);
    }

    public function categoria(): BelongsTo
    {
        return $this->belongsTo(Categoria::class)->withTrashed();
    }

    public function historicos(): HasMany
    {
        return $this->hasMany(JustificativaHistorico::class);
    }

    public function anexos(): HasMany
    {
        return $this->hasMany(JustificativaAnexo::class);
    }

    public function grauParentesco(): BelongsTo
    {
        return $this->belongsTo(GrauParentesco::class)->withTrashed();
    }
}
