@extends('layouts.app')

@section('title', 'Justificativas Finalizadas')

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet">
<link href="https://cdn.datatables.net/2.3.2/css/dataTables.bootstrap5.min.css" rel="stylesheet">
@endpush

@section('content')
<div class="page-header"><div><h1 class="page-title">Justificativas Finalizadas</h1><p class="page-description">Justificativas aprovadas ou rejeitadas.</p></div></div>
<div class="card content-card mb-3"><div class="card-header"><h5>Filtros</h5></div><div class="card-body"><div class="row g-3 align-items-end">
    <div class="col-md-4"><label for="filtroColaborador" class="form-label">Colaborador</label><select id="filtroColaborador" class="form-select"><option value="">Todos</option>@foreach($colaboradores as $colaborador)<option value="{{ $colaborador->id }}">{{ $colaborador->nome }}</option>@endforeach</select></div>
    <div class="col-md-2"><label for="filtroControle" class="form-label">Controle</label><select id="filtroControle" class="form-select"><option value="">Todos</option><option value="aprovado">Aprovado</option><option value="reprovado">Reprovado</option></select></div>
    <div class="col-md-3"><label for="filtroCategoria" class="form-label">Categoria</label><select id="filtroCategoria" class="form-select"><option value="">Todas</option>@foreach($categorias as $categoria)<option value="{{ $categoria->id }}">{{ $categoria->nome }}</option>@endforeach</select></div>
    <div class="col-md-3"><label for="filtroTexto" class="form-label">Busca</label><input id="filtroTexto" class="form-control" placeholder="Categoria, descrição ou mensagem do RH"></div>
</div></div></div>
<div class="card content-card"><div class="card-header"><h5>Resultados</h5></div><div class="card-body p-0"><div class="table-responsive">
    <table id="finalizadasTable" class="table table-hover align-middle w-100"><thead><tr><th>ID</th><th>Colaborador</th><th>Gestor</th><th>Descrição</th><th>Categoria</th><th>Data e hora da justificativa</th><th>Última situação</th><th>Mensagem RH</th><th>Última alteração</th><th class="text-center" data-dt-order="disable">Ações</th></tr></thead><tbody></tbody></table>
</div></div></div>
@endsection

@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.datatables.net/2.3.2/js/dataTables.min.js"></script>
<script src="https://cdn.datatables.net/2.3.2/js/dataTables.bootstrap5.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    $('#filtroColaborador, #filtroCategoria').select2({theme:'bootstrap-5',allowClear:true,width:'100%'});
    const tabela = new DataTable('#finalizadasTable', {processing:true,serverSide:true,searching:false,ajax:{url:@json(route('justificativas.finalizadas')),data:d=>{d.colaborador_id=$('#filtroColaborador').val();d.controle=$('#filtroControle').val();d.categoria_id=$('#filtroCategoria').val();d.texto=document.getElementById('filtroTexto').value;}},columns:[{data:'id'},{data:'colaborador'},{data:'gestor'},{data:'descricao'},{data:'categoria'},{data:'data_hora_justificativa'},{data:'situacao'},{data:'mensagem_rh'},{data:'atualizado_em'},{data:'acoes'}],columnDefs:[{targets:[0,5,6,8],className:'text-nowrap'},{targets:9,orderable:false,searchable:false,className:'text-center text-nowrap'}],order:[[8,'desc']],pageLength:10,lengthMenu:[10,25,50],language:{emptyTable:'Nenhuma justificativa finalizada.',info:'Exibindo _START_ a _END_ de _TOTAL_ justificativas',infoEmpty:'Nenhuma justificativa encontrada',infoFiltered:'(filtrado de _MAX_ justificativas)',lengthMenu:'Exibir _MENU_ registros',zeroRecords:'Nenhuma justificativa encontrada.',paginate:{first:'Primeira',last:'Última',next:'Próxima',previous:'Anterior'}}});
    $('#filtroColaborador, #filtroControle, #filtroCategoria').on('change',()=>tabela.ajax.reload());
    let atraso; document.getElementById('filtroTexto').addEventListener('input',()=>{clearTimeout(atraso);atraso=setTimeout(()=>tabela.ajax.reload(),350)});
});
</script>
@endpush
