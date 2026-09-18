<div class="d-inline-flex gap-1">
    @if($giPermissoes->permite('justificativas_colaborador.visualizar'))
        <a href="{{ route('justificativas_colaborador.show', $justificativa) }}" class="btn btn-sm btn-outline-dark" title="Visualizar" aria-label="Visualizar"><i class="bi bi-eye-fill" aria-hidden="true"></i></a>
    @endif
    @if($giPermissoes->permite('justificativas_colaborador.historico'))
        <a href="{{ route('justificativas_colaborador.historico', $justificativa) }}" class="btn btn-sm btn-outline-secondary" title="Histórico" aria-label="Histórico"><i class="bi bi-clock-history" aria-hidden="true"></i></a>
    @endif
    @if($justificativa->controle === 'colaborador')
        @if($giPermissoes->permite('justificativas_colaborador.editar'))
            <a href="{{ route('justificativas_colaborador.edit', $justificativa) }}" class="btn btn-sm btn-outline-primary" title="Editar" aria-label="Editar"><i class="bi bi-pencil-fill" aria-hidden="true"></i></a>
        @endif
        @if($giPermissoes->permite('justificativas_colaborador.excluir'))
            <form method="POST" action="{{ route('justificativas_colaborador.destroy', $justificativa) }}" onsubmit="return confirm('Deseja excluir esta justificativa?')">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger" title="Excluir" aria-label="Excluir"><i class="bi bi-trash-fill" aria-hidden="true"></i></button></form>
        @endif
        @if($giPermissoes->permite('justificativas_colaborador.enviar_responsavel'))
            <button type="button" class="btn btn-sm btn-primary alterar-controle" data-url="{{ route('justificativas_colaborador.enviar', $justificativa) }}" data-acao="encaminhar" data-confirmacao="Deseja enviar esta justificativa ao responsável? Após o envio, você não poderá alterá-la.">Enviar p/ Responsavel</button>
        @endif
    @endif
</div>
