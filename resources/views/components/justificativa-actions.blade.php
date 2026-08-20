<div class="d-inline-flex gap-1">
@if($giPermissoes->permite('justificativa.historico'))<a href="{{ route('justificativas.historico',$justificativa->id) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-clock-history"></i></a>@endif
@if($justificativa->trashed())
    @if($giPermissoes->permite('justificativa.restaurar'))<form method="POST" action="{{ route('justificativas.restore',$justificativa->id) }}">@csrf @method('PATCH')<button class="btn btn-sm btn-outline-success"><i class="bi bi-arrow-counterclockwise"></i></button></form>@endif
    @if($giPermissoes->permite('justificativa.excluir_definitivamente'))<form method="POST" action="{{ route('justificativas.force-destroy',$justificativa->id) }}" onsubmit="return confirm('Esta exclusão será definitiva. Deseja continuar?')">@csrf @method('DELETE')<button class="btn btn-sm btn-danger"><i class="bi bi-trash3-fill"></i></button></form>@endif
@else
    @if($giPermissoes->permite('justificativa.visualizar'))<a href="{{ route('justificativas.show',$justificativa) }}" class="btn btn-sm btn-outline-dark"><i class="bi bi-eye-fill"></i></a>@endif
    @if($justificativa->controle === 'colaborador' && $giPermissoes->permite('justificativa.editar'))<a href="{{ route('justificativas.edit',$justificativa) }}" class="btn btn-sm btn-outline-primary" title="Editar"><i class="bi bi-pencil-fill"></i></a>@endif
    @if($justificativa->controle === 'colaborador' && $giPermissoes->permite('justificativa.excluir'))<form method="POST" action="{{ route('justificativas.destroy',$justificativa) }}" onsubmit="return confirm('Deseja excluir esta justificativa?')">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger" title="Excluir"><i class="bi bi-trash-fill"></i></button></form>@endif
    @if($justificativa->controle === 'colaborador')
        @php($gestor = $justificativa->colaborador?->responsavel?->colaborador?->nome)
        <button type="button" class="btn btn-sm btn-primary alterar-controle" data-url="{{ route('justificativas.controle', $justificativa) }}" data-acao="encaminhar" data-confirmacao="{{ $gestor ? 'Certeza que deseja enviar para o Gestor Responsável '.$gestor.'?' : 'Não existe gestor responsável associado. Certeza que deseja encaminhar diretamente para o RH?' }}">Encaminhar para {{ $gestor ? 'Gestão' : 'RH' }}</button>
    @elseif($justificativa->controle === 'gestao')
        <button type="button" class="btn btn-sm btn-primary alterar-controle" data-url="{{ route('justificativas.controle', $justificativa) }}" data-acao="encaminhar" data-confirmacao="Certeza que deseja encaminhar esta justificativa para o RH?">Encaminhar para RH</button>
        <button type="button" class="btn btn-sm btn-outline-warning alterar-controle" data-url="{{ route('justificativas.controle', $justificativa) }}" data-acao="devolver_colaborador" data-confirmacao="Certeza que deseja devolver esta justificativa para o colaborador?">Devolver p/ Colaborador</button>
        <button type="button" class="btn btn-sm btn-danger alterar-controle" data-url="{{ route('justificativas.controle', $justificativa) }}" data-acao="rejeitar_gestao" data-confirmacao="Certeza que deseja rejeitar esta justificativa na Gestão?">Rejeitar</button>
    @elseif($justificativa->controle === 'rh')
        <button type="button" class="btn btn-sm btn-success alterar-controle" data-url="{{ route('justificativas.controle', $justificativa) }}" data-acao="aprovar" data-confirmacao="Certeza que deseja aprovar esta justificativa?">Aprovar</button>
        <button type="button" class="btn btn-sm btn-danger alterar-controle" data-url="{{ route('justificativas.controle', $justificativa) }}" data-acao="reprovar" data-mensagem="required" data-confirmacao="Certeza que deseja reprovar esta justificativa?">Reprovar</button>
        <button type="button" class="btn btn-sm btn-outline-warning alterar-controle" data-url="{{ route('justificativas.controle', $justificativa) }}" data-acao="devolver_correcao" data-mensagem="required" data-confirmacao="Certeza que deseja devolver esta justificativa para correção da gestão?">Devolver para correção</button>
    @endif
@endif
</div>
