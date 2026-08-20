<div class="d-inline-flex gap-1">
    @if($giPermissoes->permite('justificativa.visualizar'))<a href="{{ route('justificativas.show', $justificativa) }}" class="btn btn-sm btn-outline-dark" title="Visualizar"><i class="bi bi-eye-fill"></i></a>@endif
    @if($giPermissoes->permite('justificativa.historico'))<a href="{{ route('justificativas.historico', $justificativa) }}" class="btn btn-sm btn-outline-secondary" title="Histórico"><i class="bi bi-clock-history"></i></a>@endif
</div>
