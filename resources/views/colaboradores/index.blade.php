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
                    <tbody>
                    @foreach ($colaboradores as $colaborador)
                        <tr>
                            <td>{{ $colaborador->id }}</td>
                            <td>{{ $colaborador->nome }}</td>
                            <td>{{ $colaborador->email }}</td>
                            <td>{{ $colaborador->perfil }}</td>
                            <td>{{ $colaborador->perfil_id }}</td>
                            <td><span class="badge {{ $colaborador->ativo ? 'text-bg-success' : 'text-bg-secondary' }}">{{ $colaborador->ativo ? 'Ativo' : 'Inativo' }}</span></td>
                            <td class="text-nowrap">{{ $colaborador->updated_at?->format('d/m/Y H:i') ?? '—' }}</td>
                            <td class="text-center text-nowrap">
                                <div class="d-inline-flex gap-1">
                                    @if ($giPermissoes->permite('colaboradores.visualizar'))
                                        <a href="{{ route('colaboradores.show', $colaborador) }}" class="btn btn-sm btn-outline-dark listagem-acao" title="Visualizar colaborador" aria-label="Visualizar {{ $colaborador->nome }}"><i class="bi bi-eye-fill"></i></a>
                                    @endif
                                    @if ($giPermissoes->permite('colaboradores.editar'))
                                        <a href="{{ route('colaboradores.edit', $colaborador) }}" class="btn btn-sm btn-outline-primary listagem-acao" title="Editar colaborador" aria-label="Editar {{ $colaborador->nome }}"><i class="bi bi-pencil-fill"></i></a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
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
        document.addEventListener('DOMContentLoaded', () => new DataTable('#colaboradoresTable', {
            columnDefs: [
                { targets: [0, 4, 5, 6], className: 'text-nowrap' },
                { targets: 7, orderable: false, searchable: false, className: 'text-center text-nowrap' }
            ],
            order: [[1, 'asc']],
            pageLength: 10,
            language: {
                emptyTable: 'Nenhum colaborador cadastrado.', info: 'Exibindo _START_ a _END_ de _TOTAL_ colaboradores',
                infoEmpty: 'Nenhum colaborador encontrado', infoFiltered: '(filtrado de _MAX_ colaboradores)',
                lengthMenu: 'Exibir _MENU_ registros', search: 'Pesquisar:',
                zeroRecords: 'Nenhum colaborador encontrado.', paginate: { first: 'Primeira', last: 'Última', next: 'Próxima', previous: 'Anterior' }
            }
        }));
    </script>
@endpush
