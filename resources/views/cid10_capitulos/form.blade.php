@extends('layouts.app')
@php($editando = $capitulo->exists)
@section('title', ($editando ? 'Editar' : 'Cadastrar').' capítulo CID-10')
@section('content')
<div class="page-header"><div><h1 class="page-title">{{ $editando ? 'Editar' : 'Cadastrar' }} capítulo CID-10</h1><p class="page-description">Informe os dados do capítulo.</p></div></div>
<form method="POST" action="{{ $editando ? route('cid10_capitulos.update', $capitulo) : route('cid10_capitulos.store') }}">@csrf @if($editando) @method('PUT') @endif
<div class="card content-card"><div class="card-header"><h5>Dados do capítulo</h5></div><div class="card-body">@if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $erro)<li>{{ $erro }}</li>@endforeach</ul></div>@endif<div class="row g-3">
<div class="col-md-3"><label for="numcap" class="form-label">Número do capítulo</label><input type="number" id="numcap" name="numcap" class="form-control @error('numcap') is-invalid @enderror" value="{{ old('numcap', $capitulo->numcap) }}">@error('numcap')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
<div class="col-md-3"><label for="catinic" class="form-label">Categoria inicial</label><input id="catinic" name="catinic" maxlength="255" class="form-control" value="{{ old('catinic', $capitulo->catinic) }}"></div>
<div class="col-md-3"><label for="catfim" class="form-label">Categoria final</label><input id="catfim" name="catfim" maxlength="255" class="form-control" value="{{ old('catfim', $capitulo->catfim) }}"></div>
<div class="col-md-3"><label for="ativo" class="form-label">Situação <span class="text-danger">*</span></label><select id="ativo" name="ativo" class="form-select" required><option value="1" @selected((string)old('ativo',(int)($capitulo->ativo ?? true))==='1')>Ativo</option><option value="0" @selected((string)old('ativo',(int)($capitulo->ativo ?? true))==='0')>Inativo</option></select></div>
<div class="col-md-6"><label for="descricao" class="form-label">Descrição</label><input id="descricao" name="descricao" maxlength="255" class="form-control" value="{{ old('descricao', $capitulo->descricao) }}"></div>
<div class="col-md-6"><label for="descrabrev" class="form-label">Descrição abreviada</label><input id="descrabrev" name="descrabrev" maxlength="255" class="form-control" value="{{ old('descrabrev', $capitulo->descrabrev) }}"></div>
</div><div class="d-flex justify-content-end gap-2 mt-4">@if($giPermissoes->permite('cid10_capitulos.listar'))<a href="{{ route('cid10_capitulos.index') }}" class="btn btn-outline-secondary">Cancelar</a>@endif<button class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Salvar</button></div></div></div></form>
@endsection
