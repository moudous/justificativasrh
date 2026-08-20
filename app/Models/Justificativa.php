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
        'tipo_ocorrencia',
        'data_ocorrencia',
        'hora_inicial',
        'hora_final',
        'data_inicial',
        'numero_dias',
        'data_retorno',
        'status',
        'controle',
        'mensagem_rh',
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
        'data_ocorrencia' => 'date',
        'data_inicial' => 'date',
        'numero_dias' => 'integer',
        'data_retorno' => 'date',
        'atestado_medico' => 'boolean',
        'grau_parentesco_id' => 'integer',
    ];

    protected static function booted(): void
    {
        static::created(function (Justificativa $justificativa): void {
            $justificativa->historicos()->create([
                'evento' => 'criada',
                'etapa_controle' => 'colaborador',
                'historico' => 'Justificativa criada pelo colaborador',
            ]);
        });

        static::updated(function (Justificativa $justificativa): void {
            if ($justificativa->wasChanged('controle')) {
                return;
            }

            $statusAlterado = $justificativa->wasChanged('status');

            $justificativa->historicos()->create([
                'evento' => $statusAlterado ? 'status_alterado' : 'alterada',
                'etapa_controle' => $justificativa->controle,
                'historico' => $statusAlterado ? 'Situação da justificativa alterada' : 'Justificativa editada',
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
