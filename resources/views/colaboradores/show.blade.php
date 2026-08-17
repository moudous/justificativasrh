@extends('layouts.app')

@section('title', 'Visualizar colaborador')

@section('content')
    @if (session('status'))<div class="alert alert-success">{{ session('status') }}</div>@endif
    <div class="mb-4"><h1 class="page-title">Visualizar colaborador</h1><p class="page-description mb-0">Consulte os dados do colaborador.</p></div>
    <div class="card content-card">
        <div class="card-header"><h2 class="h5 fw-bold mb-0">Dados do colaborador</h2></div>
        <div class="card-body">
            @php($campos = [['ID do usuário no GI', $colaborador->id], ['Nome', $colaborador->nome], ['E-mail', $colaborador->email], ['Perfil', $colaborador->perfil], ['ID do perfil', $colaborador->perfil_id], ['Status', $colaborador->ativo ? 'Ativo' : 'Inativo'], ['Data de cadastro', $colaborador->created_at?->format('d/m/Y H:i')], ['Última alteração', $colaborador->updated_at?->format('d/m/Y H:i')]])
            <div class="row g-3">
                @foreach ($campos as [$rotulo, $valor])
                    <div class="col-md-6"><div class="form-label">{{ $rotulo }}</div><div class="form-control bg-body-tertiary h-auto text-break">{{ filled($valor) ? $valor : '—' }}</div></div>
                @endforeach
            </div>
            <div class="d-flex justify-content-end gap-2 mt-4">
                @if ($giPermissoes->permite('colaboradores.editar'))
                    <a href="{{ route('colaboradores.edit', $colaborador) }}" class="btn btn-primary"><i class="bi bi-pencil-fill me-1"></i>Editar</a>
                @endif
                @if ($giPermissoes->permite('colaboradores.listar'))
                    <a href="{{ route('colaboradores.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Voltar</a>
                @endif
            </div>
        </div>
    </div>
@endsection
