@extends('layouts.app')

@section('title', 'Colaboradores')

@push('styles')
    <link href="https://cdn.datatables.net/2.3.2/css/dataTables.bootstrap5.min.css" rel="stylesheet">
@endpush

@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">Colaboradores</h1>
            <p class="page-description">Consulte os colaboradores sincronizados automaticamente ao acessarem pelo GI.</p>
        </div>
    </div>

    <div class="card content-card">
        <div class="card-header">
            <h5>Colaboradores cadastrados</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table id="colaboradoresTable" class="table table-hover align-middle w-100">
                    <thead><tr><th>ID GI</th><th>Nome</th><th>E-mail</th><th>Perfil</th><th>ID perfil</th><th>Status</th><th>Última sincronização</th><th class="text-center" data-dt-order="disable">Ações</th></tr></thead>
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
        document.addEventListener('DOMContentLoaded', () => new DataTable('#colaboradoresTable', { processing: true, serverSide: true, ajax: '{{ route('colaboradores.index') }}', columns: [{data:'id'},{data:'nome'},{data:'email'},{data:'perfil'},{data:'perfil_id'},{data:'situacao'},{data:'atualizado_em'},{data:'acoes'}],
            columnDefs: [
                { targets: [0, 4, 5, 6], className: 'text-nowrap' },
                { targets: 7, orderable: false, searchable: false, className: 'text-center text-nowrap' }
            ],
            order: [[1, 'asc']],
            paging: true,
            pageLength: 10,
            lengthMenu: [10],
            language: {
                emptyTable: 'Nenhum colaborador cadastrado.', info: 'Exibindo _START_ a _END_ de _TOTAL_ colaboradores',
                infoEmpty: 'Nenhum colaborador encontrado', infoFiltered: '(filtrado de _MAX_ colaboradores)',
                lengthMenu: 'Exibir _MENU_ registros', search: 'Pesquisar:',
                zeroRecords: 'Nenhum colaborador encontrado.', paginate: { first: 'Primeira', last: 'Última', next: 'Próxima', previous: 'Anterior' }
            }
        }));
    </script>
@endpush
