<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\Colaborador;
use App\Models\GrauParentesco;
use App\Models\Justificativa;
use App\Models\JustificativaAnexo;
use App\Models\Cid10Subcategoria;
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
        if ($request->ajax()) {
            $podeVerInformacoesMedicas = app(GiPermissionService::class)->permite('justificativa.info_medicas', $request);
            $query = Justificativa::query()->with(['categoria', 'grauParentesco'])->withTrashed()
                ->leftJoin('categorias', 'categorias.id', '=', 'justificativas.categoria_id')
                ->leftJoin('graus_parentescos', 'graus_parentescos.id', '=', 'justificativas.grau_parentesco_id')->select('justificativas.*');
            $columns = ['justificativas.id', 'justificativas.descricao', 'categorias.nome', 'justificativas.status'];
            if ($podeVerInformacoesMedicas) array_push($columns, 'justificativas.crm_medico', 'justificativas.cid', 'justificativas.tipo_atestado', 'graus_parentescos.nome');
            array_push($columns, 'justificativas.updated_at', null);
            return $dataTable->response($request, $query, $columns, function ($registro) use ($podeVerInformacoesMedicas): array {
                $data = [
                'id' => $registro->id,
                'descricao' => e(\Illuminate\Support\Str::limit($registro->descricao, 90)).($registro->trashed() ? ' <span class="badge text-bg-danger">Excluída</span>' : ''),
                'categoria' => $registro->categoria?->nome ?? '—',
                'situacao' => '<span class="badge text-bg-info">'.e($registro->status).'</span>',
                ];
                if ($podeVerInformacoesMedicas) {
                    $data += ['crm' => $registro->crm_medico ?? '—', 'cid' => $registro->cid ?? '—', 'tipo_atestado' => match ($registro->tipo_atestado) {'proprio' => 'Próprio', 'acompanhamento' => 'Acompanhamento', default => '—'}, 'grau_parentesco' => $registro->grauParentesco?->nome ?? '—'];
                }
                $data += ['atualizado_em' => $registro->updated_at?->format('d/m/Y H:i') ?? '—', 'acoes' => view('components.justificativa-actions', ['justificativa' => $registro])->render()];
                return $data;
            });
        }
        return view('justificativas.index', [
            'colaboradorLogado' => $this->colaboradorLogado($request),
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
        $dados['tipo_atestado'] = $dados['atestado_medico'] ? ($dados['tipo_atestado'] ?? null) : null;
        $dados['crm_medico'] = $dados['atestado_medico'] ? ($dados['crm_medico'] ?? null) : null;
        $dados['cid'] = $dados['atestado_medico'] ? ($dados['cid'] ?? null) : null;
        $dados['grau_parentesco_id'] = $dados['tipo_atestado'] === 'acompanhamento' ? ($dados['grau_parentesco_id'] ?? null) : null;

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
        $dados = $request->validate($this->rules(false));
        $dados['tipo_atestado'] = $dados['atestado_medico'] ? ($dados['tipo_atestado'] ?? null) : null;
        $dados['crm_medico'] = $dados['atestado_medico'] ? ($dados['crm_medico'] ?? null) : null;
        $dados['cid'] = $dados['atestado_medico'] ? ($dados['cid'] ?? null) : null;
        $dados['grau_parentesco_id'] = $dados['tipo_atestado'] === 'acompanhamento' ? ($dados['grau_parentesco_id'] ?? null) : null;

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

    public function show(int $justificativa): View
    {
        return view('justificativas.show', [
            'justificativa' => Justificativa::query()
                ->with(['colaborador', 'categoria', 'grauParentesco', 'anexos'])
                ->findOrFail($justificativa),
        ]);
    }

    public function destroy(int $justificativa): RedirectResponse
    {
        Justificativa::query()->findOrFail($justificativa)->delete();

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

    public function historico(int $justificativa): View
    {
        $justificativa = Justificativa::withTrashed()
            ->with(['historicos' => fn ($query) => $query->latest()])
            ->findOrFail($justificativa);

        return view('justificativas.historico', compact('justificativa'));
    }

    public function anexo(int $justificativa, int $anexo): BinaryFileResponse
    {
        Justificativa::withTrashed()->findOrFail($justificativa);
        $anexo = JustificativaAnexo::query()->where('justificativa_id', $justificativa)->findOrFail($anexo);
        abort_unless(Storage::disk('local')->exists($anexo->caminho), 404);

        return response()->file(Storage::disk('local')->path($anexo->caminho), [
            'Content-Type' => $anexo->mime,
        ]);
    }

    public function destroyAnexo(Request $request, Justificativa $justificativa, int $anexo): RedirectResponse|JsonResponse
    {
        $justificativa->anexos()->findOrFail($anexo)->delete();

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Anexo excluído com sucesso.']);
        }

        return back()->with('status', 'Anexo excluído com sucesso.');
    }

    private function rules(bool $anexoObrigatorio = true): array
    {
        return [
            'descricao' => ['required', 'string'],
            'categoria_id' => ['required', 'integer', Rule::exists('categorias', 'id')->whereNull('deleted_at')],
            'anexos' => [$anexoObrigatorio ? 'required' : 'nullable', 'array'],
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

    private function cidSelecionado(?string $codigo): ?Cid10Subcategoria
    {
        return $codigo ? Cid10Subcategoria::query()->where('subcat', $codigo)->first() : null;
    }
}
