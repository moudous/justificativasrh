@extends('layouts.app')
@section('title', 'Colaboradores da Equipe')
@push('styles')
<link href="https://cdn.datatables.net/2.3.2/css/dataTables.bootstrap5.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet">
@endpush
@section('content')
@if(session('status'))<div class="alert alert-success">{{ session('status') }}</div>@endif
@if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $erro)<li>{{ $erro }}</li>@endforeach</ul></div>@endif
<div class="page-header">
    <div><h1 class="page-title">Equipe de colaboradores do responsável {{ $responsavel->colaborador?->nome ?? '—' }}</h1><p class="page-description">Consulte e gerencie os colaboradores da equipe.</p></div>
    @if($giPermissoes->permite('responsaveis.listar'))<a href="{{ route('responsaveis.index') }}" class="btn btn-outline-secondary">Voltar</a>@endif
</div>
<div class="card content-card mb-4">
    <div class="card-header"><h5>Informações do responsável</h5></div>
    <div class="card-body">
        <div class="row g-3 mb-3">
            <div class="col-md-6"><div class="form-label">Setor</div><div>{{ $responsavel->colaborador?->setor?->nome ?? '—' }}</div></div>
            <div class="col-md-6"><div class="form-label">Unidade</div><div>{{ $responsavel->colaborador?->setor?->unidade?->nome ?? '—' }}</div></div>
        </div>
        <div class="form-label">Setores sob responsabilidade</div>
        @forelse($responsavel->setores as $setor)
            <div>{{ $setor->nome }} — {{ $setor->unidade?->nome ?? 'Unidade não informada' }}</div>
        @empty
            <div>Nenhum setor vinculado.</div>
        @endforelse
    </div>
</div>
@if($giPermissoes->permite('responsaveis.equipe.criar'))
<div class="card content-card mb-4">
    <div class="card-header"><h5>Adicionar colaborador</h5></div>
    <div class="card-body">
        <form method="POST" action="{{ route('responsaveis.equipe.store', $responsavel) }}">
            @csrf
            <label for="colaborador_id" class="form-label">Colaborador</label>
            <div class="row g-3 align-items-start">
                <div class="col-md-9"><select id="colaborador_id" name="colaborador_id" class="form-select" required><option value=""></option></select><div class="form-text">Pesquise colaboradores ativos que ainda não possuem responsável.</div></div>
                <div class="col-md-3"><button class="btn btn-primary" type="submit"><i class="bi bi-plus-lg me-1" aria-hidden="true"></i>Adicionar à equipe</button></div>
            </div>
        </form>
    </div>
</div>
@endif
<div class="card content-card">
    <div class="card-header"><h5>Colaboradores da equipe</h5></div>
    <div class="card-body p-0"><div class="table-responsive">
        <table id="equipeTable" class="table table-hover align-middle w-100"><thead><tr><th>ID</th><th>Nome</th><th>Nº de justificativas</th>@if($giPermissoes->permite('responsaveis.equipe.excluir'))<th>Ações</th>@endif</tr></thead><tbody></tbody></table>
    </div></div>
</div>
@endsection
@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/2.3.2/js/dataTables.min.js"></script>
<script src="https://cdn.datatables.net/2.3.2/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    new DataTable('#equipeTable', {
        processing: true, serverSide: true, ajax: @json(route('responsaveis.equipe.index', $responsavel)),
        columns: [{data: 'id'}, {data: 'nome'}, {data: 'justificativas_count'},
            @if($giPermissoes->permite('responsaveis.equipe.excluir'))
            {data: 'acoes', orderable: false, searchable: false, className: 'text-center text-nowrap'},
            @endif
        ],
        order: [[1, 'asc']], pageLength: 10, lengthMenu: [10, 25, 50],
        language: {emptyTable: 'Nenhum colaborador na equipe.', info: 'Exibindo _START_ a _END_ de _TOTAL_ colaboradores', infoEmpty: 'Nenhum colaborador encontrado', infoFiltered: '(filtrado de _MAX_ colaboradores)', lengthMenu: 'Exibir _MENU_ registros', search: 'Pesquisar:', zeroRecords: 'Nenhum colaborador encontrado.', paginate: {first: 'Primeira', last: 'Última', next: 'Próxima', previous: 'Anterior'}}
    });
    @if($giPermissoes->permite('responsaveis.equipe.criar'))
    $('#colaborador_id').select2({theme: 'bootstrap-5', placeholder: 'Pesquise pelo nome ou e-mail', minimumInputLength: 1, width: '100%', language: {inputTooShort: () => 'Digite ao menos um caractere', noResults: () => 'Nenhum colaborador disponível', searching: () => 'Pesquisando…'}, ajax: {url: @json(route('responsaveis.equipe.search', $responsavel)), dataType: 'json', delay: 300, data: params => ({q: params.term}), processResults: data => data}});
    @endif
});
</script>
@endpush
