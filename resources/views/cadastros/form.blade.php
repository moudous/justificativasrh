@extends('layouts.app')

@php($editando = $registro->exists)
@section('title', ($editando ? 'Editar ' : 'Cadastrar ').$singular)

@section('content')
    <div class="page-header"><div><h1 class="page-title">{{ $editando ? 'Editar' : 'Cadastrar' }} {{ $singular }}</h1><p class="page-description">{{ $editando ? 'Atualize' : 'Informe' }} os dados do {{ $singular }}.</p></div></div>
    <form method="POST" action="{{ $editando ? route($recurso.'.update', $registro) : route($recurso.'.store') }}">
        @csrf
        @if($editando) @method('PUT') @endif
        <div class="card content-card">
            <div class="card-header"><h5>Dados do {{ $singular }}</h5></div>
            <div class="card-body">
                @if ($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach ($errors->all() as $erro)<li>{{ $erro }}</li>@endforeach</ul></div>@endif
                <div class="row g-3">
                    @if($editando)<div class="col-md-6"><label class="form-label" for="id">ID</label><input id="id" class="form-control" value="{{ $registro->id }}" disabled></div>@endif
                    <div class="col-md-6"><label class="form-label" for="nome">Nome</label><input id="nome" name="nome" class="form-control @error('nome') is-invalid @enderror" value="{{ old('nome', $registro->nome) }}" required maxlength="255" autofocus>@error('nome')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                    @if($temUnidade)
                        <div class="col-md-6"><label class="form-label" for="unidade_id">Unidade</label><select id="unidade_id" name="unidade_id" class="form-select @error('unidade_id') is-invalid @enderror" required><option value="">Selecione</option>@foreach($unidades as $unidade)<option value="{{ $unidade->id }}" @selected((string) old('unidade_id', $registro->unidade_id) === (string) $unidade->id)>{{ $unidade->nome }}</option>@endforeach</select>@error('unidade_id')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                    @endif
                    <div class="col-md-6"><label class="form-label" for="ativo">Status</label><select id="ativo" name="ativo" class="form-select @error('ativo') is-invalid @enderror" required><option value="1" @selected((string) old('ativo', (int) ($registro->ativo ?? true)) === '1')>Ativo</option><option value="0" @selected((string) old('ativo', (int) ($registro->ativo ?? true)) === '0')>Inativo</option></select>@error('ativo')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                </div>
                <div class="d-flex flex-wrap justify-content-end gap-2 mt-4"><a href="{{ route($recurso.'.index') }}" class="btn btn-outline-secondary">Cancelar</a><button class="btn btn-primary" type="submit"><i class="bi bi-check-lg me-1"></i>Salvar</button></div>
            </div>
        </div>
    </form>
@endsection
