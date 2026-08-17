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
                    <tbody>
                    @foreach ($registros as $registro)
                        <tr class="{{ $registro->trashed() ? 'table-light text-muted' : '' }}">
                            <td>{{ $registro->id }}</td>
                            <td>{{ $registro->nome }} @if($registro->trashed())<span class="badge text-bg-danger ms-1">Excluído</span>@endif</td>
                            @if($temUnidade)<td>{{ $registro->unidade?->nome ?? '—' }} @if($registro->unidade?->trashed())<span class="badge text-bg-light border">Unidade excluída</span>@endif</td>@endif
                            <td><span class="badge {{ $registro->ativo ? 'text-bg-success' : 'text-bg-secondary' }}">{{ $registro->ativo ? 'Ativo' : 'Inativo' }}</span></td>
                            <td class="text-nowrap">{{ $registro->updated_at?->format('d/m/Y H:i') ?? '—' }}</td>
                            <td class="text-center text-nowrap">
                                <div class="d-inline-flex gap-1">
                                    @if ($registro->trashed())
                                        @if ($giPermissoes->permite($permissao.'.restaurar'))
                                            <form method="POST" action="{{ route($recurso.'.restore', $registro->id) }}">@csrf @method('PATCH')<button class="btn btn-sm btn-outline-success listagem-acao" title="Restaurar {{ $singular }}" aria-label="Restaurar {{ $registro->nome }}"><i class="bi bi-arrow-counterclockwise"></i></button></form>
                                        @endif
                                        @if ($giPermissoes->permite($permissao.'.excluir_definitivamente'))
                                            <form method="POST" action="{{ route($recurso.'.force-destroy', $registro->id) }}" onsubmit="return confirm('A exclusão de {{ addslashes($registro->nome) }} será definitiva e não poderá ser desfeita. Deseja continuar?')">@csrf @method('DELETE')<button class="btn btn-sm btn-danger listagem-acao" title="Excluir definitivamente {{ $singular }}" aria-label="Excluir definitivamente {{ $registro->nome }}"><i class="bi bi-trash3-fill"></i></button></form>
                                        @endif
                                    @else
                                        @if ($giPermissoes->permite($permissao.'.visualizar'))
                                            <a href="{{ route($recurso.'.show', $registro) }}" class="btn btn-sm btn-outline-dark listagem-acao" title="Visualizar {{ $singular }}" aria-label="Visualizar {{ $registro->nome }}"><i class="bi bi-eye-fill"></i></a>
                                        @endif
                                        @if ($giPermissoes->permite($permissao.'.editar'))
                                            <a href="{{ route($recurso.'.edit', $registro) }}" class="btn btn-sm btn-outline-primary listagem-acao" title="Editar {{ $singular }}" aria-label="Editar {{ $registro->nome }}"><i class="bi bi-pencil-fill"></i></a>
                                            <form method="POST" action="{{ route($recurso.'.toggle', $registro) }}">@csrf @method('PATCH')<button class="btn btn-sm {{ $registro->ativo ? 'btn-outline-warning' : 'btn-outline-success' }} listagem-acao" title="{{ $registro->ativo ? 'Desativar' : 'Ativar' }} {{ $singular }}" aria-label="{{ $registro->ativo ? 'Desativar' : 'Ativar' }} {{ $registro->nome }}"><i class="bi bi-toggle-on"></i></button></form>
                                        @endif
                                        @if ($giPermissoes->permite($permissao.'.excluir'))
                                            <form method="POST" action="{{ route($recurso.'.destroy', $registro) }}" onsubmit="return confirm('Deseja excluir {{ addslashes($registro->nome) }}?')">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger listagem-acao" title="Excluir {{ $singular }}" aria-label="Excluir {{ $registro->nome }}"><i class="bi bi-trash-fill"></i></button></form>
                                        @endif
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
        document.addEventListener('DOMContentLoaded', () => new DataTable('#cadastrosTable', {
            columnDefs: [{ targets: [0, {{ $temUnidade ? 3 : 2 }}, {{ $temUnidade ? 4 : 3 }}], className: 'text-nowrap' }, { targets: {{ $temUnidade ? 5 : 4 }}, orderable: false, searchable: false, className: 'text-center text-nowrap' }],
            order: [[1, 'asc']], pageLength: 10,
            language: { emptyTable: 'Nenhum {{ $singular }} cadastrado.', info: 'Exibindo _START_ a _END_ de _TOTAL_ {{ $plural }}', infoEmpty: 'Nenhum registro encontrado', infoFiltered: '(filtrado de _MAX_ registros)', lengthMenu: 'Exibir _MENU_ registros', search: 'Pesquisar:', zeroRecords: 'Nenhum registro encontrado.', paginate: { first: 'Primeira', last: 'Última', next: 'Próxima', previous: 'Anterior' } }
        }));
    </script>
@endpush
