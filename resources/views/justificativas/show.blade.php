@extends('layouts.app')

@section('title', 'Visualizar justificativa')

@section('content')
    @if(session('status'))<div class="alert alert-success">{{ session('status') }}</div>@endif
    <div class="page-header"><div><h1 class="page-title">Visualizar justificativa</h1><p class="page-description">Consulte os dados da justificativa.</p></div></div>
    <div class="card content-card"><div class="card-header"><h5>Dados da justificativa</h5></div><div class="card-body">
        @php($campos = [['Descrição', $justificativa->descricao, 'col-12'], ['ID', $justificativa->id], ['Colaborador', $justificativa->colaborador?->nome], ['Categoria', $justificativa->categoria?->nome], ['Status', $justificativa->status], ['Atestado médico', $justificativa->atestado_medico ? 'Sim' : 'Não'], ['Tipo do atestado', match($justificativa->tipo_atestado) {'proprio' => 'Próprio', 'acompanhamento' => 'Acompanhamento de familiar', default => '—'}], ['Grau de parentesco', $justificativa->grauParentesco?->nome], ['Data de cadastro', $justificativa->created_at?->format('d/m/Y H:i')], ['Última alteração', $justificativa->updated_at?->format('d/m/Y H:i')]])
        <div class="row g-3">@foreach($campos as $campo) @php([$rotulo, $valor, $coluna] = [$campo[0], $campo[1], $campo[2] ?? 'col-md-6'])<div class="{{ $coluna }}"><div class="form-label">{{ $rotulo }}</div><div class="form-control bg-body-tertiary h-auto text-break">{{ filled($valor) ? $valor : '—' }}</div></div>@endforeach</div>
        <div class="d-flex justify-content-end gap-2 mt-4"><a href="{{ route('justificativas.anexo', $justificativa) }}" target="_blank" class="btn btn-outline-dark"><i class="bi bi-paperclip me-1"></i>Visualizar anexo</a>@if($giPermissoes->permite('justificativa.historico'))<a href="{{ route('justificativas.historico', $justificativa) }}" class="btn btn-outline-primary"><i class="bi bi-clock-history me-1"></i>Histórico</a>@endif @if($giPermissoes->permite('justificativa.listar'))<a href="{{ route('justificativas.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Voltar</a>@endif</div>
    </div></div>
@endsection
