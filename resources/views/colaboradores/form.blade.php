@extends('layouts.app')

@section('title', 'Editar colaborador')

@push('styles')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet">
@endpush

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
                    <div class="col-md-6"><label class="form-label" for="nome">Nome</label><input id="nome" class="form-control" value="{{ $colaborador->nome }}" disabled></div>
                    <div class="col-md-6"><label class="form-label" for="email">E-mail</label><input type="email" id="email" class="form-control" value="{{ $colaborador->email }}" disabled></div>
                    <div class="col-md-6"><label class="form-label" for="perfil">Perfil</label><input id="perfil" class="form-control" value="{{ $colaborador->perfil }}" disabled></div>
                    <div class="col-md-6"><label class="form-label" for="perfil_id">ID do perfil</label><input id="perfil_id" class="form-control" value="{{ $colaborador->perfil_id }}" disabled></div>
                    <div class="col-md-6"><label class="form-label" for="ativo">Status</label><select id="ativo" name="ativo" class="form-select" required><option value="1" @selected((string) old('ativo', (int) $colaborador->ativo) === '1')>Ativo</option><option value="0" @selected((string) old('ativo', (int) $colaborador->ativo) === '0')>Inativo</option></select></div>
                    <div class="col-md-6"><label class="form-label" for="setor_id">Setor</label><select id="setor_id" name="setor_id" class="form-select @error('setor_id') is-invalid @enderror"><option value="">Selecione o setor</option>@foreach($setores as $setor)<option value="{{ $setor->id }}" @selected((string) old('setor_id', $colaborador->setor_id) === (string) $setor->id)>{{ $setor->nome }}</option>@endforeach</select>@error('setor_id')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                    <div class="col-md-6"><label class="form-label" for="responsavel_id">Responsável</label><select id="responsavel_id" name="responsavel_id" class="form-select @error('responsavel_id') is-invalid @enderror"><option value="">Sem responsável</option>@foreach($responsaveis as $responsavel)<option value="{{ $responsavel->id }}" data-setor-id="{{ $responsavel->setores->count() === 1 ? $responsavel->setores->first()->id : '' }}" @selected((string) old('responsavel_id', $colaborador->responsavel_id) === (string) $responsavel->id)>{{ $responsavel->colaborador?->nome ?? 'Colaborador não encontrado' }}</option>@endforeach</select>@error('responsavel_id')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                </div>
                <div class="d-flex flex-wrap justify-content-end gap-2 mt-4">@if ($giPermissoes->permite('colaboradores.visualizar'))<a href="{{ route('colaboradores.show', $colaborador) }}" class="btn btn-outline-secondary">Cancelar</a>@elseif ($giPermissoes->permite('colaboradores.listar'))<a href="{{ route('colaboradores.index') }}" class="btn btn-outline-secondary">Cancelar</a>@endif<button class="btn btn-primary" type="submit"><i class="bi bi-check-lg me-1"></i>Salvar</button></div>
            </div>
        </div>
    </form>
@endsection

@push('scripts')
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const setor = $('#setor_id');
            const responsavel = $('#responsavel_id');

            setor.select2({ theme: 'bootstrap-5', placeholder: 'Pesquise um setor', allowClear: true, width: '100%' });
            responsavel.select2({ theme: 'bootstrap-5', placeholder: 'Pesquise um responsável', allowClear: true, width: '100%' });

            responsavel.on('change', function () {
                if (setor.val()) return;

                const setorId = this.options[this.selectedIndex]?.dataset.setorId;
                if (setorId) setor.val(setorId).trigger('change');
            });
        });
    </script>
@endpush
