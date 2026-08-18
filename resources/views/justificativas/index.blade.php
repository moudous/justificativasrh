@extends('layouts.app')

@section('title', 'Minhas Justificativas')

@push('styles')
    <link href="https://cdn.datatables.net/2.3.2/css/dataTables.bootstrap5.min.css" rel="stylesheet">
@endpush

@section('content')
    @php($podeVerInformacoesMedicas = $giPermissoes->permite('justificativa.info_medicas'))
    @if (session('status'))<div class="alert alert-success">{{ session('status') }}</div>@endif
    <div class="page-header">
        <div><h1 class="page-title">Minhas Justificativas</h1><p class="page-description">Gestão de justificativas cadastradas por {{ $colaboradorLogado->nome }}</p></div>
        @if ($giPermissoes->permite('justificativa.criar'))<a href="{{ route('justificativas.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Cadastrar justificativa</a>@endif
    </div>
    <div class="card content-card">
        <div class="card-header"><h5>Justificativas cadastradas</h5></div>
        <div class="card-body p-0"><div class="table-responsive">
            <table id="justificativasTable" class="table table-hover align-middle w-100">
                <thead><tr><th>ID</th><th>Descrição</th><th>Categoria</th><th>Situação</th>@if($podeVerInformacoesMedicas)<th>CRM</th><th>CID</th><th>Tipo do atestado</th><th>Grau de parentesco</th>@endif<th>Última alteração</th><th class="text-center" data-dt-order="disable">Ações</th></tr></thead>
                <tbody></tbody>
            </table>
        </div></div>
    </div>
@endsection

@push('scripts')
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/2.3.2/js/dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/2.3.2/js/dataTables.bootstrap5.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => new DataTable('#justificativasTable', { processing: true, serverSide: true, ajax: '{{ route('justificativas.index') }}', columns: [{data:'id'},{data:'descricao'},{data:'categoria'},{data:'situacao'},@if($podeVerInformacoesMedicas){data:'crm'},{data:'cid'},{data:'tipo_atestado'},{data:'grau_parentesco'},@endif{data:'atualizado_em'},{data:'acoes'}],
            columnDefs: [{ targets: [0, 3, {{ $podeVerInformacoesMedicas ? 8 : 4 }}], className: 'text-nowrap' }, { targets: {{ $podeVerInformacoesMedicas ? 9 : 5 }}, orderable: false, searchable: false, className: 'text-center text-nowrap' }],
            order: [[0, 'desc']], paging: true, pageLength: 10,
            lengthMenu: [10],
            language: { emptyTable: 'Nenhuma justificativa cadastrada.', info: 'Exibindo _START_ a _END_ de _TOTAL_ justificativas', infoEmpty: 'Nenhuma justificativa encontrada', infoFiltered: '(filtrado de _MAX_ justificativas)', lengthMenu: 'Exibir _MENU_ registros', search: 'Pesquisar:', zeroRecords: 'Nenhuma justificativa encontrada.', paginate: { first: 'Primeira', last: 'Última', next: 'Próxima', previous: 'Anterior' } }
        }));
    </script>
@endpush
