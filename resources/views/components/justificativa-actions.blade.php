<div class="d-inline-flex gap-1">
@if($giPermissoes->permite('justificativa.historico'))<a href="{{ route('justificativas.historico',$justificativa->id) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-clock-history"></i></a>@endif
@if($justificativa->trashed())
    @if($giPermissoes->permite('justificativa.restaurar'))<form method="POST" action="{{ route('justificativas.restore',$justificativa->id) }}">@csrf @method('PATCH')<button class="btn btn-sm btn-outline-success"><i class="bi bi-arrow-counterclockwise"></i></button></form>@endif
    @if($giPermissoes->permite('justificativa.excluir_definitivamente'))<form method="POST" action="{{ route('justificativas.force-destroy',$justificativa->id) }}" onsubmit="return confirm('Esta exclusão será definitiva. Deseja continuar?')">@csrf @method('DELETE')<button class="btn btn-sm btn-danger"><i class="bi bi-trash3-fill"></i></button></form>@endif
@else
    @if($giPermissoes->permite('justificativa.visualizar'))<a href="{{ route('justificativas.show',$justificativa) }}" class="btn btn-sm btn-outline-dark"><i class="bi bi-eye-fill"></i></a>@endif
    @if($giPermissoes->permite('justificativa.editar'))<a href="{{ route('justificativas.edit',$justificativa) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil-fill"></i></a>@endif
    @if($giPermissoes->permite('justificativa.excluir'))<form method="POST" action="{{ route('justificativas.destroy',$justificativa) }}" onsubmit="return confirm('Deseja excluir esta justificativa?')">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash-fill"></i></button></form>@endif
@endif
</div>
