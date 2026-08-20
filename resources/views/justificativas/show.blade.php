@extends('layouts.app')

@section('title', 'Visualizar justificativa')

@section('content')
    @if(session('status'))<div class="alert alert-success">{{ session('status') }}</div>@endif
    <div class="page-header"><div><h1 class="page-title">Visualizar justificativa</h1><p class="page-description">Consulte os dados da justificativa.</p></div></div>
    <div class="card content-card"><div class="card-header"><h5>Dados da justificativa</h5></div><div class="card-body">
        @php
            $campos = [['Descrição', $justificativa->descricao, 'col-12'], ['ID', $justificativa->id], ['Colaborador', $justificativa->colaborador?->nome], ['Categoria/Ocorrência', $justificativa->categoria?->nome], ['Tipo da ocorrência', $justificativa->tipo_ocorrencia === 'intervalo' ? 'Intervalo da ocorrência' : 'Data da ocorrência'], ['Data da ocorrência', $justificativa->tipo_ocorrencia === 'data' ? $justificativa->data_ocorrencia?->format('d/m/Y') : null], ['Horário', $justificativa->tipo_ocorrencia === 'data' && $justificativa->hora_inicial ? substr($justificativa->hora_inicial, 0, 5).' às '.substr($justificativa->hora_final, 0, 5) : null], ['Data inicial', $justificativa->tipo_ocorrencia === 'intervalo' ? $justificativa->data_inicial?->format('d/m/Y') : null], ['Número de dias', $justificativa->tipo_ocorrencia === 'intervalo' ? $justificativa->numero_dias : null], ['Data de retorno', $justificativa->tipo_ocorrencia === 'intervalo' ? $justificativa->data_retorno?->format('d/m/Y') : null], ['Situação', $justificativa->status], ['Data de cadastro', $justificativa->created_at?->format('d/m/Y H:i')], ['Última alteração', $justificativa->updated_at?->format('d/m/Y H:i')]];
            if ($giPermissoes->permite('justificativa.info_medicas')) {
                array_splice($campos, 5, 0, [
                    ['Atestado médico', $justificativa->atestado_medico ? 'Sim' : 'Não'],
                    ['CRM médico(a)', $justificativa->crm_medico],
                    ['CID', $justificativa->cid],
                    ['Tipo do atestado', match($justificativa->tipo_atestado) {'proprio' => 'Próprio', 'acompanhamento' => 'Acompanhamento de familiar', default => '—'}],
                    ['Grau de parentesco', $justificativa->tipo_atestado === 'acompanhamento' ? $justificativa->grauParentesco?->nome : null],
                ]);
            }
        @endphp
        <div class="row g-3">@foreach($campos as $campo) @php([$rotulo, $valor, $coluna] = [$campo[0], $campo[1], $campo[2] ?? 'col-md-6'])<div class="{{ $coluna }}"><div class="form-label">{{ $rotulo }}</div><div class="form-control bg-body-tertiary h-auto text-break">{{ filled($valor) ? $valor : '—' }}</div></div>@endforeach</div>
        @if($giPermissoes->permite('justificativa.anexos.visualizar'))<div class="form-label mt-3">Anexos</div><div class="row g-2">@forelse($justificativa->anexos as $anexo)<div class="col-6 col-md-4 col-lg-3"><a href="{{ route('justificativas.anexo', [$justificativa, $anexo]) }}" class="card h-100 text-decoration-none abrir-anexo-viewer" data-nome="{{ $anexo->nome_original }}" data-colaborador="{{ $justificativa->colaborador?->nome ?? '—' }}" data-mime="{{ $anexo->mime }}">@if(str_starts_with($anexo->mime, 'image/'))<img src="{{ route('justificativas.anexo', [$justificativa, $anexo]) }}" class="card-img-top object-fit-cover" style="height:150px" alt="{{ $anexo->nome_original }}">@else<div class="d-flex align-items-center justify-content-center bg-light text-danger" style="height:150px"><i class="bi bi-file-earmark-pdf-fill" style="font-size:4rem"></i></div>@endif<div class="card-body p-2 small text-truncate">{{ $anexo->nome_original }}</div></a></div>@empty<div class="col-12 text-muted">Nenhum anexo.</div>@endforelse</div>@endif
        <div class="d-flex justify-content-end gap-2 mt-4">@if($giPermissoes->permite('justificativa.editar'))<a href="{{ route('justificativas.edit', $justificativa) }}" class="btn btn-primary"><i class="bi bi-pencil-fill me-1"></i>Editar</a>@endif @if($giPermissoes->permite('justificativa.historico'))<a href="{{ route('justificativas.historico', $justificativa) }}" class="btn btn-outline-primary"><i class="bi bi-clock-history me-1"></i>Histórico</a>@endif @if($giPermissoes->permite('justificativa.listar'))<a href="{{ route('justificativas.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Voltar</a>@endif</div>
    </div></div>
@include('components.anexo-viewer')
@endsection
