@extends('layouts.app')

@section('title', 'Histórico da justificativa')

@section('content')
    <div class="page-header"><div><h1 class="page-title">Histórico da justificativa</h1><p class="page-description">Justificativa #{{ $justificativa->id }}</p></div></div>
    <div class="card content-card"><div class="card-header"><h5>Eventos registrados</h5></div><div class="card-body p-0"><div class="table-responsive">
        <table class="table table-hover align-middle"><thead><tr><th>Data e hora</th><th>Etapa de Controle</th><th>Histórico</th><th>Mensagem RH</th></tr></thead><tbody>
            @forelse($justificativa->historicos as $historico)
                <tr><td class="text-nowrap">{{ $historico->created_at?->format('d/m/Y H:i:s') ?? '—' }}</td><td><span class="badge text-bg-info">{{ match($historico->etapa_controle) {'colaborador' => 'Colaborador', 'gestao' => 'Gestão', 'rh' => 'RH', default => '—'} }}</span></td><td>{{ $historico->historico ?? '—' }}</td><td>{{ $historico->mensagem_rh ?? '—' }}</td></tr>
            @empty<tr><td colspan="4" class="text-center text-muted">Nenhum evento registrado.</td></tr>@endforelse
        </tbody></table>
    </div></div></div>
    <div class="d-flex justify-content-end mt-4">@if($giPermissoes->permite('justificativa.listar'))<a href="{{ route('justificativas.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Voltar</a>@endif</div>
@endsection
