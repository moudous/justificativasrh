@extends('layouts.app')

@section('title', 'Editar colaborador')

@section('content')
    <div class="page-header"><div><h1 class="page-title">Editar colaborador</h1><p class="page-description">Atualize os dados do colaborador.</p></div></div>
    <form method="POST" action="{{ route('colaboradores.update', $colaborador) }}">
        @csrf
        @method('PUT')
        <div class="card content-card">
            <div class="card-header"><h5>Dados do colaborador</h5></div>
            <div class="card-body">
                @if ($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach ($errors->all() as $erro)<li>{{ $erro }}</li>@endforeach</ul></div>@endif
                <div class="row g-3">
                    <div class="col-md-6"><label class="form-label" for="id">ID do usuário no GI</label><input id="id" class="form-control" value="{{ $colaborador->id }}" disabled></div>
                    <div class="col-md-6"><label class="form-label" for="nome">Nome</label><input id="nome" name="nome" class="form-control @error('nome') is-invalid @enderror" value="{{ old('nome', $colaborador->nome) }}" required maxlength="255">@error('nome')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                    <div class="col-md-6"><label class="form-label" for="email">E-mail</label><input type="email" id="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $colaborador->email) }}" required>@error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                    <div class="col-md-6"><label class="form-label" for="perfil">Perfil</label><input id="perfil" name="perfil" class="form-control @error('perfil') is-invalid @enderror" value="{{ old('perfil', $colaborador->perfil) }}" required>@error('perfil')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                    <div class="col-md-6"><label class="form-label" for="perfil_id">ID do perfil</label><input type="number" min="1" id="perfil_id" name="perfil_id" class="form-control @error('perfil_id') is-invalid @enderror" value="{{ old('perfil_id', $colaborador->perfil_id) }}" required>@error('perfil_id')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                    <div class="col-md-6"><label class="form-label" for="ativo">Status</label><select id="ativo" name="ativo" class="form-select" required><option value="1" @selected((string) old('ativo', (int) $colaborador->ativo) === '1')>Ativo</option><option value="0" @selected((string) old('ativo', (int) $colaborador->ativo) === '0')>Inativo</option></select></div>
                </div>
                <div class="d-flex flex-wrap justify-content-end gap-2 mt-4">@if ($giPermissoes->permite('colaboradores.visualizar'))<a href="{{ route('colaboradores.show', $colaborador) }}" class="btn btn-outline-secondary">Cancelar</a>@elseif ($giPermissoes->permite('colaboradores.listar'))<a href="{{ route('colaboradores.index') }}" class="btn btn-outline-secondary">Cancelar</a>@endif<button class="btn btn-primary" type="submit"><i class="bi bi-check-lg me-1"></i>Salvar</button></div>
            </div>
        </div>
    </form>
@endsection
