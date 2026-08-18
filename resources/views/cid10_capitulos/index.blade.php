@extends('layouts.app')
@section('title', 'Capítulos CID-10')
@push('styles')<link href="https://cdn.datatables.net/2.3.2/css/dataTables.bootstrap5.min.css" rel="stylesheet">@endpush
@section('content')
@if(session('status'))<div class="alert alert-success">{{ session('status') }}</div>@endif
<div class="page-header"><div><h1 class="page-title">Capítulos CID-10</h1><p class="page-description">Gerencie os capítulos da Classificação Internacional de Doenças.</p></div>@if($giPermissoes->permite('cid10_capitulos.criar'))<a href="{{ route('cid10_capitulos.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Cadastrar capítulo</a>@endif</div>
<div class="card content-card"><div class="card-header"><h5>Capítulos cadastrados</h5></div><div class="card-body p-0"><div class="table-responsive"><table id="capitulosTable" class="table table-hover align-middle w-100">
<thead><tr><th>ID</th><th>Capítulo</th><th>Categoria inicial</th><th>Categoria final</th><th>Descrição</th><th>Descrição abreviada</th><th>Situação</th><th class="text-center" data-dt-order="disable">Ações</th></tr></thead><tbody></tbody></table></div></div></div>
@endsection
@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script><script src="https://cdn.datatables.net/2.3.2/js/dataTables.min.js"></script><script src="https://cdn.datatables.net/2.3.2/js/dataTables.bootstrap5.min.js"></script>
<script>document.addEventListener('DOMContentLoaded',()=>new DataTable('#capitulosTable',{processing:true,serverSide:true,ajax:'{{ route('cid10_capitulos.index') }}',columns:[{data:'id'},{data:'numcap'},{data:'catinic'},{data:'catfim'},{data:'descricao'},{data:'descrabrev'},{data:'situacao'},{data:'acoes'}],columnDefs:[{targets:[0,1,2,3,6],className:'text-nowrap'},{targets:7,orderable:false,searchable:false,className:'text-center text-nowrap'}],order:[[1,'asc']],paging:true,pageLength:10,lengthMenu:[10],language:{emptyTable:'Nenhum capítulo cadastrado.',info:'Exibindo _START_ a _END_ de _TOTAL_ capítulos',infoEmpty:'Nenhum capítulo encontrado',infoFiltered:'(filtrado de _MAX_ capítulos)',lengthMenu:'Exibir _MENU_ registros',search:'Pesquisar:',zeroRecords:'Nenhum capítulo encontrado.',paginate:{first:'Primeira',last:'Última',next:'Próxima',previous:'Anterior'}}}));</script>
@endpush
