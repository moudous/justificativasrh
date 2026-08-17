@extends('layouts.app')

@section('title', 'Visualizar '.$singular)

@section('content')
    @if (session('status'))<div class="alert alert-success">{{ session('status') }}</div>@endif
    <div class="page-header"><div><h1 class="page-title">Visualizar {{ $singular }}</h1><p class="page-description">Consulte os dados do {{ $singular }}.</p></div></div>
    <div class="card content-card">
        <div class="card-header"><h5>Dados do {{ $singular }}</h5></div>
        <div class="card-body">
            @php($campos = array_filter([['ID', $registro->id], ['Nome', $registro->nome], $temUnidade ? ['Unidade', $registro->unidade?->nome] : null, ['Status', $registro->ativo ? 'Ativo' : 'Inativo'], ['Data de cadastro', $registro->created_at?->format('d/m/Y H:i')], ['Última alteração', $registro->updated_at?->format('d/m/Y H:i')]]))
            <div class="row g-3">@foreach ($campos as [$rotulo, $valor])<div class="col-md-6"><div class="form-label">{{ $rotulo }}</div><div class="form-control bg-body-tertiary h-auto text-break">{{ filled($valor) ? $valor : '—' }}</div></div>@endforeach</div>
            <div class="d-flex justify-content-end gap-2 mt-4">
                @if ($giPermissoes->permite($permissao.'.editar'))<a href="{{ route($recurso.'.edit', $registro) }}" class="btn btn-primary"><i class="bi bi-pencil-fill me-1"></i>Editar</a>@endif
                @if ($giPermissoes->permite($permissao.'.listar'))<a href="{{ route($recurso.'.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Voltar</a>@endif
            </div>
        </div>
    </div>
@endsection
