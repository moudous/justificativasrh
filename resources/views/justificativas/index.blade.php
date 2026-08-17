@extends('layouts.app')

@section('title', 'Justificativas')

@push('styles')
    <link href="https://cdn.datatables.net/2.3.2/css/dataTables.bootstrap5.min.css" rel="stylesheet">
@endpush

@section('content')
    @if (session('status'))<div class="alert alert-success">{{ session('status') }}</div>@endif
    <div class="page-header">
        <div><h1 class="page-title">Justificativas</h1><p class="page-description">Gerencie as justificativas cadastradas.</p></div>
        @if ($giPermissoes->permite('justificativa.criar'))<a href="{{ route('justificativas.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Cadastrar justificativa</a>@endif
    </div>
    <div class="card content-card">
        <div class="card-header"><h5>Justificativas cadastradas</h5></div>
        <div class="card-body p-0"><div class="table-responsive">
            <table id="justificativasTable" class="table table-hover align-middle w-100">
                <thead><tr><th>ID</th><th>Descrição</th><th>Colaborador</th><th>Categoria</th><th>Status</th><th>Última alteração</th><th class="text-center" data-dt-order="disable">Ações</th></tr></thead>
                <tbody>
                @foreach($justificativas as $justificativa)
                    <tr class="{{ $justificativa->trashed() ? 'table-light text-muted' : '' }}">
                        <td>{{ $justificativa->id }}</td>
                        <td>{{ \Illuminate\Support\Str::limit($justificativa->descricao, 90) }} @if($justificativa->trashed())<span class="badge text-bg-danger ms-1">Excluída</span>@endif</td>
                        <td>{{ $justificativa->colaborador?->nome ?? '—' }}</td>
                        <td>{{ $justificativa->categoria?->nome ?? '—' }} @if($justificativa->categoria?->trashed())<span class="badge text-bg-light border">Excluída</span>@endif</td>
                        <td><span class="badge text-bg-info">{{ $justificativa->status }}</span></td>
                        <td class="text-nowrap">{{ $justificativa->updated_at?->format('d/m/Y H:i') ?? '—' }}</td>
                        <td class="text-center text-nowrap"><div class="d-inline-flex gap-1">
                            @if($giPermissoes->permite('justificativa.historico'))<a href="{{ route('justificativas.historico', $justificativa->id) }}" class="btn btn-sm btn-outline-secondary listagem-acao" title="Histórico da justificativa" aria-label="Histórico da justificativa {{ $justificativa->id }}"><i class="bi bi-clock-history"></i></a>@endif
                            @if($justificativa->trashed())
                                @if($giPermissoes->permite('justificativa.restaurar'))<form method="POST" action="{{ route('justificativas.restore', $justificativa->id) }}">@csrf @method('PATCH')<button class="btn btn-sm btn-outline-success listagem-acao" title="Restaurar justificativa" aria-label="Restaurar justificativa {{ $justificativa->id }}"><i class="bi bi-arrow-counterclockwise"></i></button></form>@endif
                                @if($giPermissoes->permite('justificativa.excluir_definitivamente'))<form method="POST" action="{{ route('justificativas.force-destroy', $justificativa->id) }}" onsubmit="return confirm('Esta exclusão será definitiva e também removerá o histórico. Deseja continuar?')">@csrf @method('DELETE')<button class="btn btn-sm btn-danger listagem-acao" title="Excluir definitivamente" aria-label="Excluir definitivamente justificativa {{ $justificativa->id }}"><i class="bi bi-trash3-fill"></i></button></form>@endif
                            @else
                                @if($giPermissoes->permite('justificativa.visualizar'))<a href="{{ route('justificativas.show', $justificativa) }}" class="btn btn-sm btn-outline-dark listagem-acao" title="Visualizar justificativa" aria-label="Visualizar justificativa {{ $justificativa->id }}"><i class="bi bi-eye-fill"></i></a>@endif
                                @if($giPermissoes->permite('justificativa.excluir'))<form method="POST" action="{{ route('justificativas.destroy', $justificativa) }}" onsubmit="return confirm('Deseja excluir esta justificativa?')">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger listagem-acao" title="Excluir justificativa" aria-label="Excluir justificativa {{ $justificativa->id }}"><i class="bi bi-trash-fill"></i></button></form>@endif
                            @endif
                        </div></td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div></div>
    </div>
@endsection

@push('scripts')
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/2.3.2/js/dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/2.3.2/js/dataTables.bootstrap5.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => new DataTable('#justificativasTable', {
            columnDefs: [{ targets: [0, 4, 5], className: 'text-nowrap' }, { targets: 6, orderable: false, searchable: false, className: 'text-center text-nowrap' }],
            order: [[0, 'desc']], pageLength: 10,
            language: { emptyTable: 'Nenhuma justificativa cadastrada.', info: 'Exibindo _START_ a _END_ de _TOTAL_ justificativas', infoEmpty: 'Nenhuma justificativa encontrada', infoFiltered: '(filtrado de _MAX_ justificativas)', lengthMenu: 'Exibir _MENU_ registros', search: 'Pesquisar:', zeroRecords: 'Nenhuma justificativa encontrada.', paginate: { first: 'Primeira', last: 'Última', next: 'Próxima', previous: 'Anterior' } }
        }));
    </script>
@endpush
