@if($giPermissoes->permite('responsaveis.equipe.excluir'))
<form method="POST" action="{{ route('responsaveis.equipe.destroy', [$responsavel, $colaborador]) }}" onsubmit="return confirm('Deseja remover este colaborador da equipe?')">
    @csrf @method('DELETE')
    <button type="submit" class="btn btn-sm btn-outline-danger" title="Remover da equipe" aria-label="Remover da equipe"><i class="bi bi-person-dash-fill" aria-hidden="true"></i></button>
</form>
@endif
