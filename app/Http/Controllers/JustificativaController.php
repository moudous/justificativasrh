<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\Colaborador;
use App\Models\GrauParentesco;
use App\Models\Justificativa;
use App\Models\JustificativaAnexo;
use App\Models\Cid10Subcategoria;
use App\Models\Responsavel;
use App\Services\DataTableServer;
use App\Services\GiPermissionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class JustificativaController extends Controller
{
    public function index(Request $request, DataTableServer $dataTable): View|JsonResponse
    {
        $colaborador = $this->colaboradorLogado($request);

        return $this->listagem($request, $dataTable, 'colaborador', 'Minhas Justificativas',
            'Justificativas que ainda podem ser editadas e encaminhadas.', route('justificativas.index'), $colaborador->id);
    }

    public function gestao(Request $request, DataTableServer $dataTable): View|JsonResponse
    {
        $responsavelId = Responsavel::query()->where('colaborador_id', $this->colaboradorLogado($request)->id)->value('id');

        return $this->listagem($request, $dataTable, 'gestao', 'Justificativas da Gestão',
            'Justificativas dos colaboradores sob sua responsabilidade.', route('justificativas.gestao'), null, $responsavelId ?? 0);
    }

    public function rh(Request $request, DataTableServer $dataTable): View|JsonResponse
    {
        return $this->listagem($request, $dataTable, 'rh', 'Justificativas do RH',
            'Justificativas aguardando análise do RH.', route('justificativas.rh'));
    }

    public function finalizadas(Request $request, DataTableServer $dataTable): View|JsonResponse
    {
        $usuario = $this->colaboradorLogado($request);
        $responsavelId = Responsavel::query()->where('colaborador_id', $usuario->id)->value('id');
        $ehRh = mb_strtolower(trim($usuario->perfil)) === 'rh';
        $idsPermitidos = $ehRh ? null : Colaborador::query()
            ->where(fn ($query) => $query->whereKey($usuario->id)->when($responsavelId, fn ($filtro) => $filtro->orWhere('responsavel_id', $responsavelId)))
            ->pluck('id');

        $queryBase = Justificativa::query()
            ->with(['categoria', 'colaborador.responsavel.colaborador', 'historicos' => fn ($query) => $query->latest()])
            ->join('colaboradores', 'colaboradores.id', '=', 'justificativas.colaborador_id')
            ->leftJoin('categorias', 'categorias.id', '=', 'justificativas.categoria_id')
            ->whereIn('justificativas.controle', ['aprovado', 'reprovado'])
            ->when($idsPermitidos !== null, fn ($query) => $query->whereIn('justificativas.colaborador_id', $idsPermitidos))
            ->select('justificativas.*');

        if ($request->ajax()) {
            $query = clone $queryBase;
            $query->when($request->integer('colaborador_id'), fn ($filtro, $id) => $filtro->where('justificativas.colaborador_id', $id));
            $query->when(in_array($request->input('controle'), ['aprovado', 'reprovado'], true) ? $request->input('controle') : null, fn ($filtro, $controle) => $filtro->where('justificativas.controle', $controle));
            $query->when($request->integer('categoria_id'), fn ($filtro, $id) => $filtro->where('justificativas.categoria_id', $id));
            $texto = trim((string) $request->input('texto', ''));
            $query->when($texto !== '', fn ($filtro) => $filtro->where(fn ($busca) => $busca
                ->where('categorias.nome', 'like', '%'.$texto.'%')
                ->orWhere('justificativas.descricao', 'like', '%'.$texto.'%')
                ->orWhere('justificativas.mensagem_rh', 'like', '%'.$texto.'%')));

            $columns = ['justificativas.id', 'colaboradores.nome', null, 'justificativas.descricao', 'categorias.nome', null, 'justificativas.controle', 'justificativas.mensagem_rh', 'justificativas.updated_at', null];

            return $dataTable->response($request, $query, $columns, fn ($registro): array => [
                'id' => $registro->id,
                'colaborador' => $registro->colaborador?->nome ?? '—',
                'gestor' => $registro->colaborador?->responsavel?->colaborador?->nome ?? '—',
                'descricao' => e(\Illuminate\Support\Str::limit($registro->descricao, 90)),
                'categoria' => $registro->categoria?->nome ?? '—',
                'data_hora_justificativa' => $this->formatarOcorrencia($registro),
                'situacao' => '<span class="badge '.($registro->controle === 'aprovado' ? 'text-bg-success' : 'text-bg-danger').'">'.e($registro->historicos->first()?->historico ?? ($registro->controle === 'aprovado' ? 'Aprovada' : 'Rejeitada')).'</span>',
                'mensagem_rh' => e($registro->mensagem_rh ?: '—'),
                'atualizado_em' => $registro->updated_at?->format('d/m/Y H:i') ?? '—',
                'acoes' => view('components.justificativa-finalizada-actions', ['justificativa' => $registro])->render(),
            ]);
        }

        return view('justificativas.finalizadas', [
            'colaboradores' => Colaborador::query()->when($idsPermitidos !== null, fn ($query) => $query->whereIn('id', $idsPermitidos))->orderBy('nome')->get(['id', 'nome']),
            'categorias' => Categoria::withTrashed()->whereHas('justificativas', fn ($query) => $query->whereIn('controle', ['aprovado', 'reprovado'])->when($idsPermitidos !== null, fn ($filtro) => $filtro->whereIn('colaborador_id', $idsPermitidos)))->orderBy('nome')->get(['id', 'nome']),
        ]);
    }

    public function create(Request $request): View
    {
        return view('justificativas.form', [
            'colaborador' => $this->colaboradorLogado($request),
            'categorias' => Categoria::query()->where('ativo', true)->orderBy('nome')->get(),
            'grausParentesco' => GrauParentesco::query()->where('ativo', true)->orderBy('nome')->get(),
            'cidSelecionado' => $this->cidSelecionado($request->old('cid')),
        ]);
    }

    public function pesquisarCids(Request $request, GiPermissionService $permissoes): JsonResponse
    {
        abort_unless(
            $permissoes->permite('justificativa.criar', $request) || $permissoes->permite('justificativa.editar', $request),
            403,
        );

        $termo = trim((string) $request->input('q', ''));
        $query = Cid10Subcategoria::query()->where('ativo', true)->orderBy('subcat');
        if ($termo !== '') {
            $query->where(fn ($filtro) => $filtro->where('subcat', 'like', '%'.$termo.'%')->orWhere('descricao', 'like', '%'.$termo.'%'));
        }

        return response()->json(['results' => $query->limit(20)->get()->map(fn (Cid10Subcategoria $cid) => [
            'id' => $cid->subcat,
            'text' => $cid->subcat.' - '.$cid->descricao,
        ])]);
    }

    public function store(Request $request): RedirectResponse
    {
        $dados = $request->validate($this->rules());
        $dados['colaborador_id'] = $this->colaboradorLogado($request)->id;
        $dados['status'] = 'Ainda não enviado ao responsável';
        $dados['controle'] = 'colaborador';
        $dados['tipo_atestado'] = $dados['atestado_medico'] ? ($dados['tipo_atestado'] ?? null) : null;
        $dados['crm_medico'] = $dados['atestado_medico'] ? ($dados['crm_medico'] ?? null) : null;
        $dados['cid'] = $dados['atestado_medico'] ? ($dados['cid'] ?? null) : null;
        $dados['grau_parentesco_id'] = $dados['tipo_atestado'] === 'acompanhamento' ? ($dados['grau_parentesco_id'] ?? null) : null;
        $dados = $this->normalizarOcorrencia($dados);

        $anexos = $request->file('anexos', []);
        unset($dados['anexos']);

        try {
            $justificativa = Justificativa::query()->create($dados);
            $this->armazenarAnexos($justificativa, $anexos);
        } catch (\Throwable $erro) {
            if (isset($justificativa)) {
                $justificativa->anexos()->get()->each->delete();
                $justificativa->forceDelete();
            }
            throw $erro;
        }

        return redirect()->route('justificativas.show', $justificativa)
            ->with('status', 'Justificativa cadastrada com sucesso.');
    }

    public function edit(Request $request, Justificativa $justificativa): View
    {
        abort_unless($justificativa->controle === 'colaborador' && $justificativa->colaborador_id === $this->colaboradorLogado($request)->id, 403, 'Esta justificativa não pode ser editada por este colaborador.');
        return view('justificativas.form', [
            'justificativa' => $justificativa,
            'colaborador' => $justificativa->colaborador,
            'categorias' => Categoria::query()->where('ativo', true)->orderBy('nome')->get(),
            'grausParentesco' => GrauParentesco::query()->where('ativo', true)->orderBy('nome')->get(),
            'cidSelecionado' => $this->cidSelecionado($request->old('cid', $justificativa->cid)),
        ]);
    }

    public function update(Request $request, Justificativa $justificativa): RedirectResponse
    {
        abort_unless($justificativa->controle === 'colaborador' && $justificativa->colaborador_id === $this->colaboradorLogado($request)->id, 403, 'Esta justificativa não pode ser editada por este colaborador.');
        $dados = $request->validate($this->rules());
        $dados['tipo_atestado'] = $dados['atestado_medico'] ? ($dados['tipo_atestado'] ?? null) : null;
        $dados['crm_medico'] = $dados['atestado_medico'] ? ($dados['crm_medico'] ?? null) : null;
        $dados['cid'] = $dados['atestado_medico'] ? ($dados['cid'] ?? null) : null;
        $dados['grau_parentesco_id'] = $dados['tipo_atestado'] === 'acompanhamento' ? ($dados['grau_parentesco_id'] ?? null) : null;
        $dados = $this->normalizarOcorrencia($dados);

        $anexos = $request->file('anexos', []);
        unset($dados['anexos']);

        try {
            $justificativa->update($dados);
            $this->armazenarAnexos($justificativa, $anexos);
        } catch (\Throwable $erro) {
            throw $erro;
        }

        return redirect()->route('justificativas.show', $justificativa)
            ->with('status', 'Justificativa atualizada com sucesso.');
    }

    public function show(Request $request, int $justificativa): View
    {
        $registro = Justificativa::query()
            ->with(['colaborador', 'categoria', 'grauParentesco', 'anexos'])
            ->findOrFail($justificativa);
        $this->autorizarVisualizacao($request, $registro);

        return view('justificativas.show', [
            'justificativa' => $registro,
        ]);
    }

    public function destroy(Request $request, int $justificativa): RedirectResponse
    {
        $registro = Justificativa::query()->findOrFail($justificativa);
        abort_unless($registro->controle === 'colaborador' && $registro->colaborador_id === $this->colaboradorLogado($request)->id, 403, 'Esta justificativa não pode ser excluída por este colaborador.');
        $registro->delete();

        return redirect()->route('justificativas.index')
            ->with('status', 'Justificativa excluída com sucesso.');
    }

    public function restore(int $justificativa): RedirectResponse
    {
        Justificativa::onlyTrashed()->findOrFail($justificativa)->restore();

        return redirect()->route('justificativas.index')
            ->with('status', 'Justificativa restaurada com sucesso.');
    }

    public function forceDestroy(int $justificativa): RedirectResponse
    {
        Justificativa::onlyTrashed()->findOrFail($justificativa)->forceDelete();

        return redirect()->route('justificativas.index')
            ->with('status', 'Justificativa excluída definitivamente.');
    }

    public function historico(Request $request, int $justificativa): View
    {
        $justificativa = Justificativa::withTrashed()
            ->with(['historicos' => fn ($query) => $query->latest()])
            ->findOrFail($justificativa);
        $this->autorizarVisualizacao($request, $justificativa);

        return view('justificativas.historico', compact('justificativa'));
    }

    public function anexo(Request $request, int $justificativa, int $anexo): BinaryFileResponse
    {
        $registro = Justificativa::withTrashed()->findOrFail($justificativa);
        $this->autorizarVisualizacao($request, $registro);
        $anexo = JustificativaAnexo::query()->where('justificativa_id', $justificativa)->findOrFail($anexo);
        abort_unless(Storage::disk('local')->exists($anexo->caminho), 404);

        return response()->file(Storage::disk('local')->path($anexo->caminho), [
            'Content-Type' => $anexo->mime,
        ]);
    }

    public function destroyAnexo(Request $request, Justificativa $justificativa, int $anexo): RedirectResponse|JsonResponse
    {
        abort_unless($justificativa->controle === 'colaborador' && $justificativa->colaborador_id === $this->colaboradorLogado($request)->id, 403, 'Os anexos desta justificativa não podem ser alterados por este colaborador.');
        $justificativa->anexos()->findOrFail($anexo)->delete();

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Anexo excluído com sucesso.']);
        }

        return back()->with('status', 'Anexo excluído com sucesso.');
    }

    public function alterarControle(Request $request, Justificativa $justificativa): JsonResponse
    {
        $usuario = $this->colaboradorLogado($request);
        if ($justificativa->controle === 'colaborador') {
            abort_unless($justificativa->colaborador_id === $usuario->id, 403, 'Somente o colaborador pode encaminhar sua justificativa.');
        }
        if ($justificativa->controle === 'gestao') {
            $responsavel = $justificativa->colaborador()->with('responsavel')->first()?->responsavel;
            abort_unless($responsavel?->colaborador_id === $usuario->id, 403, 'Somente o gestor responsável pode alterar esta justificativa.');
        }

        $dados = $request->validate([
            'acao' => ['required', Rule::in(['encaminhar', 'devolver_colaborador', 'rejeitar_gestao', 'aprovar', 'reprovar', 'devolver_correcao'])],
            'mensagem_rh' => ['nullable', 'string', 'max:100', Rule::requiredIf(fn (): bool => in_array($request->input('acao'), ['reprovar', 'devolver_correcao'], true))],
        ]);

        $controleAtual = $justificativa->controle;
        $novoControle = match ([$controleAtual, $dados['acao']]) {
            ['colaborador', 'encaminhar'] => $justificativa->colaborador()->value('responsavel_id') ? 'gestao' : 'rh',
            ['gestao', 'encaminhar'] => 'rh',
            ['gestao', 'devolver_colaborador'] => 'colaborador',
            ['gestao', 'rejeitar_gestao'] => 'reprovado',
            ['rh', 'aprovar'] => 'aprovado',
            ['rh', 'reprovar'] => 'reprovado',
            ['rh', 'devolver_correcao'] => 'gestao',
            default => null,
        };

        abort_if($novoControle === null, 422, 'Esta ação não é válida para a etapa atual.');

        $descricaoHistorico = match ([$controleAtual, $dados['acao'], $novoControle]) {
            ['colaborador', 'encaminhar', 'gestao'] => 'Encaminhado ao Gestor',
            ['colaborador', 'encaminhar', 'rh'], ['gestao', 'encaminhar', 'rh'] => 'Encaminhado ao RH',
            ['gestao', 'devolver_colaborador', 'colaborador'] => 'Devolvido pelo Gestor',
            ['gestao', 'rejeitar_gestao', 'reprovado'] => 'Rejeitado pelo Gestor',
            ['rh', 'aprovar', 'aprovado'] => 'Aprovado pelo RH',
            ['rh', 'reprovar', 'reprovado'] => 'Rejeitado pelo RH',
            ['rh', 'devolver_correcao', 'gestao'] => 'Devolvido pelo RH',
        };

        $alteracoes = ['controle' => $novoControle];
        if (in_array($dados['acao'], ['reprovar', 'devolver_correcao'], true)) {
            $alteracoes['mensagem_rh'] = $dados['mensagem_rh'];
        }
        $justificativa->update($alteracoes);
        $justificativa->historicos()->create([
            'evento' => 'controle_alterado',
            'etapa_controle' => $controleAtual,
            'historico' => $descricaoHistorico,
            'mensagem_rh' => $controleAtual === 'rh' ? ($dados['mensagem_rh'] ?? null) : null,
        ]);

        return response()->json(['message' => 'Etapa da justificativa atualizada com sucesso.', 'controle' => $novoControle]);
    }

    private function listagem(Request $request, DataTableServer $dataTable, string $controle, string $titulo, string $descricao, string $ajaxUrl, ?int $colaboradorId = null, ?int $responsavelId = null): View|JsonResponse
    {
        if ($request->ajax()) {
            $mostrarColaborador = $controle !== 'colaborador';
            $query = Justificativa::query()
                ->with(['categoria', 'colaborador.responsavel.colaborador'])
                ->when($controle === 'colaborador', fn ($consulta) => $consulta->withTrashed())
                ->leftJoin('categorias', 'categorias.id', '=', 'justificativas.categoria_id')
                ->where('justificativas.controle', $controle)
                ->when($colaboradorId, fn ($consulta) => $consulta->where('justificativas.colaborador_id', $colaboradorId))
                ->when($responsavelId !== null, fn ($consulta) => $consulta->whereHas('colaborador', fn ($colaboradores) => $colaboradores->where('responsavel_id', $responsavelId)))
                ->select('justificativas.*');
            $columns = array_values(array_filter([
                'justificativas.id',
                $mostrarColaborador ? null : false,
                'justificativas.descricao',
                'categorias.nome',
                null,
                null,
                'justificativas.controle',
                'justificativas.updated_at',
                null,
            ], fn ($coluna) => $coluna !== false));

            return $dataTable->response($request, $query, $columns, fn ($registro): array => [
                'id' => $registro->id,
                'colaborador' => $registro->colaborador?->nome ?? '—',
                'descricao' => e(\Illuminate\Support\Str::limit($registro->descricao, 90)).($registro->trashed() ? ' <span class="badge text-bg-danger">Excluída</span>' : ''),
                'categoria' => $registro->categoria?->nome ?? '—',
                'data_hora_justificativa' => $this->formatarOcorrencia($registro),
                'gestor' => $registro->colaborador?->responsavel?->colaborador?->nome ?? '—',
                'situacao' => '<span class="badge text-bg-info">'.e(match ($registro->controle) {'colaborador' => 'Com o colaborador', 'gestao' => 'Na gestão', 'rh' => 'No RH', 'aprovado' => 'Aprovada', 'reprovado' => 'Reprovada', default => $registro->controle}).'</span>',
                'atualizado_em' => $registro->updated_at?->format('d/m/Y H:i') ?? '—',
                'acoes' => view('components.justificativa-actions', ['justificativa' => $registro, 'contexto' => $controle])->render(),
            ]);
        }

        return view('justificativas.index', compact('titulo', 'descricao', 'ajaxUrl', 'controle'));
    }

    private function formatarOcorrencia(Justificativa $justificativa): string
    {
        if ($justificativa->tipo_ocorrencia === 'intervalo') {
            $inicial = $justificativa->data_inicial?->format('d/m/Y') ?? '—';
            $retorno = $justificativa->data_retorno?->format('d/m/Y') ?? '—';

            return '<span class="text-nowrap"><strong>Início:</strong> '.e($inicial).'<br><strong>Retorno:</strong> '.e($retorno).'</span>';
        }

        $data = $justificativa->data_ocorrencia?->format('d/m/Y') ?? '—';
        $inicial = $justificativa->hora_inicial ? substr($justificativa->hora_inicial, 0, 5) : '—';
        $final = $justificativa->hora_final ? substr($justificativa->hora_final, 0, 5) : '—';

        return '<span class="text-nowrap"><strong>Data:</strong> '.e($data).'<br><strong>Horário:</strong> '.e($inicial).' às '.e($final).'</span>';
    }

    private function rules(): array
    {
        return [
            'descricao' => ['nullable', 'string'],
            'categoria_id' => ['required', 'integer', Rule::exists('categorias', 'id')->whereNull('deleted_at')],
            'tipo_ocorrencia' => ['required', Rule::in(['data', 'intervalo'])],
            'data_ocorrencia' => ['nullable', Rule::requiredIf(fn (): bool => request('tipo_ocorrencia') === 'data'), 'date'],
            'hora_inicial' => ['nullable', Rule::requiredIf(fn (): bool => request('tipo_ocorrencia') === 'data'), 'date_format:H:i'],
            'hora_final' => ['nullable', Rule::requiredIf(fn (): bool => request('tipo_ocorrencia') === 'data'), 'date_format:H:i', 'after:hora_inicial'],
            'data_inicial' => ['nullable', Rule::requiredIf(fn (): bool => request('tipo_ocorrencia') === 'intervalo'), 'date'],
            'numero_dias' => ['nullable', Rule::requiredIf(fn (): bool => request('tipo_ocorrencia') === 'intervalo'), 'integer', 'min:0'],
            'data_retorno' => ['nullable', Rule::requiredIf(fn (): bool => request('tipo_ocorrencia') === 'intervalo'), 'date', 'after_or_equal:data_inicial'],
            'anexos' => ['nullable', 'array'],
            'anexos.*' => ['file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:10240'],
            'atestado_medico' => ['required', 'boolean'],
            'crm_medico' => [
                'nullable',
                Rule::requiredIf(fn (): bool => request()->boolean('atestado_medico')),
                'string',
                'max:15',
            ],
            'cid' => ['nullable', 'string', 'max:7', Rule::exists('cid10_subcategorias', 'subcat')->where(fn ($query) => $query->where('ativo', true)->whereNull('deleted_at'))],
            'tipo_atestado' => [
                'nullable',
                Rule::requiredIf(fn (): bool => request()->boolean('atestado_medico')),
                Rule::in(['proprio', 'acompanhamento']),
            ],
            'grau_parentesco_id' => [
                'nullable',
                Rule::requiredIf(fn (): bool => request()->boolean('atestado_medico') && request('tipo_atestado') === 'acompanhamento'),
                'integer',
                Rule::exists('graus_parentescos', 'id')->where(fn ($query) => $query->where('ativo', true)->whereNull('deleted_at')),
            ],
        ];
    }

    private function normalizarOcorrencia(array $dados): array
    {
        if ($dados['tipo_ocorrencia'] === 'data') {
            $dados['data_inicial'] = null;
            $dados['numero_dias'] = null;
            $dados['data_retorno'] = null;
        } else {
            $dados['data_ocorrencia'] = null;
            $dados['hora_inicial'] = null;
            $dados['hora_final'] = null;
        }

        return $dados;
    }

    private function armazenarAnexos(Justificativa $justificativa, array $arquivos): void
    {
        $novosAnexos = collect();

        try {
            foreach ($arquivos as $arquivo) {
                $caminho = $arquivo->store('justificativas', 'local');
                try {
                    $novosAnexos->push($justificativa->anexos()->create([
                        'caminho' => $caminho,
                        'nome_original' => $arquivo->getClientOriginalName(),
                        'mime' => $arquivo->getMimeType(),
                    ]));
                } catch (\Throwable $erro) {
                    Storage::disk('local')->delete($caminho);
                    throw $erro;
                }
            }
        } catch (\Throwable $erro) {
            $novosAnexos->each->delete();
            throw $erro;
        }
    }

    private function colaboradorLogado(Request $request): Colaborador
    {
        $id = filter_var($request->session()->get('gi_context.usuario.id'), FILTER_VALIDATE_INT);

        abort_if($id === false || $id < 1, 401, 'O GI não informou o usuário autenticado.');

        return Colaborador::query()->findOrFail($id);
    }

    private function autorizarVisualizacao(Request $request, Justificativa $justificativa): void
    {
        $usuario = $this->colaboradorLogado($request);
        if (mb_strtolower(trim($usuario->perfil)) === 'rh' || $justificativa->colaborador_id === $usuario->id) {
            return;
        }

        $responsavelId = Responsavel::query()->where('colaborador_id', $usuario->id)->value('id');
        abort_unless($responsavelId && $justificativa->colaborador()->where('responsavel_id', $responsavelId)->exists(), 403, 'Você não pode visualizar esta justificativa.');
    }

    private function cidSelecionado(?string $codigo): ?Cid10Subcategoria
    {
        return $codigo ? Cid10Subcategoria::query()->where('subcat', $codigo)->first() : null;
    }
}
