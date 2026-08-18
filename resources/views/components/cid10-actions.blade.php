<div class="d-inline-flex gap-1">
@if($registro->trashed())
    @if($giPermissoes->permite($permissao.'.restaurar'))<form method="POST" action="{{ route($rota.'.restore', $registro->id) }}">@csrf @method('PATCH')<button class="btn btn-sm btn-outline-success listagem-acao" title="Restaurar"><i class="bi bi-arrow-counterclockwise"></i></button></form>@endif
@else
    @if($giPermissoes->permite($permissao.'.visualizar'))<a href="{{ route($rota.'.show', $registro) }}" class="btn btn-sm btn-outline-dark listagem-acao" title="Visualizar"><i class="bi bi-eye-fill"></i></a>@endif
    @if($giPermissoes->permite($permissao.'.editar'))<a href="{{ route($rota.'.edit', $registro) }}" class="btn btn-sm btn-outline-primary listagem-acao" title="Editar"><i class="bi bi-pencil-fill"></i></a><form method="POST" action="{{ route($rota.'.toggle', $registro) }}">@csrf @method('PATCH')<button class="btn btn-sm {{ $registro->ativo ? 'btn-outline-warning' : 'btn-outline-success' }} listagem-acao" title="{{ $registro->ativo ? 'Desativar' : 'Ativar' }}"><i class="bi bi-toggle-on"></i></button></form>@endif
    @if($giPermissoes->permite($permissao.'.excluir'))<form method="POST" action="{{ route($rota.'.destroy', $registro) }}" onsubmit="return confirm('Deseja excluir este registro?')">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger listagem-acao" title="Excluir"><i class="bi bi-trash-fill"></i></button></form>@endif
@endif
</div>
