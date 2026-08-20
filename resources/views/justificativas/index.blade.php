@extends('layouts.app')

@section('title', $titulo)

@push('styles')
    <link href="https://cdn.datatables.net/2.3.2/css/dataTables.bootstrap5.min.css" rel="stylesheet">
@endpush

@section('content')
    @if (session('status'))<div class="alert alert-success">{{ session('status') }}</div>@endif
    <div class="page-header">
        <div><h1 class="page-title">{{ $titulo }}</h1><p class="page-description">{{ $descricao }}</p></div>
        @if ($controle === 'colaborador' && $giPermissoes->permite('justificativa.criar'))<a href="{{ route('justificativas.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Cadastrar justificativa</a>@endif
    </div>
    <div class="card content-card">
        <div class="card-header"><h5>Justificativas cadastradas</h5></div>
        <div class="card-body p-0"><div class="table-responsive">
            <table id="justificativasTable" class="table table-hover align-middle w-100">
                <thead><tr><th>ID</th>@if($controle !== 'colaborador')<th>Colaborador</th>@endif<th>Descrição</th><th>Categoria/Ocorrência</th><th>Data e hora da justificativa</th><th>Gestor</th><th>Situação</th><th>Última alteração</th><th class="text-center" data-dt-order="disable">Ações</th></tr></thead>
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
        document.addEventListener('DOMContentLoaded', () => { const colunas = [{data:'id'}, @if($controle !== 'colaborador'){data:'colaborador'},@endif {data:'descricao'},{data:'categoria'},{data:'data_hora_justificativa'},{data:'gestor'},{data:'situacao'},{data:'atualizado_em'},{data:'acoes'}]; const tabela = new DataTable('#justificativasTable', { processing: true, serverSide: true, ajax: @json($ajaxUrl), columns: colunas,
            columnDefs: [{ targets: [0, colunas.length - 2], className: 'text-nowrap' }, { targets: colunas.length - 1, orderable: false, searchable: false, className: 'text-center text-nowrap' }],
            order: [[0, 'desc']], paging: true, pageLength: 10,
            lengthMenu: [10],
            language: { emptyTable: 'Nenhuma justificativa cadastrada.', info: 'Exibindo _START_ a _END_ de _TOTAL_ justificativas', infoEmpty: 'Nenhuma justificativa encontrada', infoFiltered: '(filtrado de _MAX_ justificativas)', lengthMenu: 'Exibir _MENU_ registros', search: 'Pesquisar:', zeroRecords: 'Nenhuma justificativa encontrada.', paginate: { first: 'Primeira', last: 'Última', next: 'Próxima', previous: 'Anterior' } }
        }); document.getElementById('justificativasTable').addEventListener('click', async evento => { const botao = evento.target.closest('.alterar-controle'); if (!botao) return; let mensagemRh = null; if (!confirm(botao.dataset.confirmacao)) return; if (botao.dataset.mensagem === 'required') { mensagemRh = prompt('Informe a mensagem do RH (máximo de 100 caracteres):'); if (mensagemRh === null) return; mensagemRh = mensagemRh.trim(); if (!mensagemRh) { alert('A mensagem do RH é obrigatória.'); return; } if (mensagemRh.length > 100) { alert('A mensagem deve ter no máximo 100 caracteres.'); return; } } botao.disabled = true; try { const resposta = await fetch(botao.dataset.url, { method: 'PATCH', headers: {'X-CSRF-TOKEN': @json(csrf_token()), 'Accept': 'application/json', 'Content-Type': 'application/json'}, body: JSON.stringify({acao: botao.dataset.acao, mensagem_rh: mensagemRh}) }); const dados = await resposta.json(); if (!resposta.ok) throw new Error(dados.message || 'Não foi possível atualizar a etapa.'); tabela.ajax.reload(null, false); } catch (erro) { alert(erro.message); botao.disabled = false; } }); });
    </script>
@endpush
