@extends('layouts.app')

@section('title', ucfirst($plural))

@push('styles')
    <link href="https://cdn.datatables.net/2.3.2/css/dataTables.bootstrap5.min.css" rel="stylesheet">
@endpush

@section('content')
    @if (session('status'))<div class="alert alert-success">{{ session('status') }}</div>@endif
    <div class="page-header">
        <div><h1 class="page-title">{{ ucfirst($plural) }}</h1><p class="page-description">Gerencie {{ $plural }} cadastrados.</p></div>
        @if ($giPermissoes->permite($permissao.'.criar'))
            <a href="{{ route($recurso.'.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Cadastrar {{ $singular }}</a>
        @endif
    </div>

    <div class="card content-card">
        <div class="card-header"><h5>{{ ucfirst($plural) }} cadastrados</h5></div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table id="cadastrosTable" class="table table-hover align-middle w-100">
                    <thead><tr><th>ID</th><th>Nome</th>@if($temUnidade)<th>Unidade</th>@endif<th>Status</th><th>Última alteração</th><th class="text-center" data-dt-order="disable">Ações</th></tr></thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/2.3.2/js/dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/2.3.2/js/dataTables.bootstrap5.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => new DataTable('#cadastrosTable', { processing: true, serverSide: true, ajax: '{{ route($recurso.'.index') }}', columns: [{data:'id'},{data:'nome'},@if($temUnidade){data:'unidade'},@endif{data:'situacao'},{data:'atualizado_em'},{data:'acoes'}],
            columnDefs: [{ targets: [0, {{ $temUnidade ? 3 : 2 }}, {{ $temUnidade ? 4 : 3 }}], className: 'text-nowrap' }, { targets: {{ $temUnidade ? 5 : 4 }}, orderable: false, searchable: false, className: 'text-center text-nowrap' }],
            order: [[1, 'asc']], paging: true, pageLength: 10,
            lengthMenu: [10],
            language: { emptyTable: 'Nenhum {{ $singular }} cadastrado.', info: 'Exibindo _START_ a _END_ de _TOTAL_ {{ $plural }}', infoEmpty: 'Nenhum registro encontrado', infoFiltered: '(filtrado de _MAX_ registros)', lengthMenu: 'Exibir _MENU_ registros', search: 'Pesquisar:', zeroRecords: 'Nenhum registro encontrado.', paginate: { first: 'Primeira', last: 'Última', next: 'Próxima', previous: 'Anterior' } }
        }));
    </script>
@endpush
