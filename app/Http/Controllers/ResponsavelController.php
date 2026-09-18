<?php

namespace App\Http\Controllers;

use App\Models\Colaborador;
use App\Models\Responsavel;
use App\Models\Setor;
use App\Services\DataTableServer;
use App\Services\GiPermissionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ResponsavelController extends Controller
{
    public function index(Request $request, DataTableServer $dataTable): View|JsonResponse
    {
        if ($request->ajax()) {
            $query = Responsavel::query()->with(['colaborador', 'setores'])->withTrashed()
                ->leftJoin('colaboradores', 'colaboradores.id', '=', 'responsaveis.colaborador_id')->select('responsaveis.*');
            if ($this->somenteMinhaEquipe()) {
                $query->where('responsaveis.colaborador_id', $this->colaboradorLogadoId());
            }

            return $dataTable->response($request, $query, ['responsaveis.id', 'colaboradores.nome', 'responsaveis.cargo', 'colaboradores.nome', null, 'responsaveis.updated_at', null], fn (Responsavel $registro) => [
                'id' => $registro->id,
                'nome' => e($registro->colaborador?->nome ?? '—').($registro->trashed() ? ' <span class="badge text-bg-danger">Excluído</span>' : ''),
                'cargo' => $registro->cargo,
                'colaborador' => $registro->colaborador?->nome ?? '—',
                'setores' => $registro->setores->pluck('nome')->join(', ') ?: '—',
                'atualizado_em' => $registro->updated_at?->format('d/m/Y H:i') ?? '—',
                'acoes' => view('components.responsavel-actions', compact('registro'))->render(),
            ]);
        }

        return view('responsaveis.index');
    }

    public function create(Request $request): View
    {
        return $this->form(new Responsavel, $request);
    }

    public function store(Request $request): RedirectResponse
    {
        $dados = $request->validate($this->rules(), $this->messages());
        $responsavel = DB::transaction(function () use ($dados): Responsavel {
            $setores = $dados['setores'];
            unset($dados['setores']);
            $responsavel = Responsavel::create($dados);
            $responsavel->setores()->sync($setores);
            return $responsavel;
        });

        return redirect()->route('responsaveis.show', $responsavel)->with('status', 'Responsável cadastrado com sucesso.');
    }

    public function show(Responsavel $responsavel): View
    {
        $this->autorizarResponsavel($responsavel);
        $responsavel->load(['colaborador', 'setores']);
        return view('responsaveis.show', compact('responsavel'));
    }

    public function edit(Request $request, Responsavel $responsavel): View
    {
        $this->autorizarResponsavel($responsavel);
        return $this->form($responsavel->load(['colaborador', 'setores']), $request);
    }

    public function update(Request $request, Responsavel $responsavel): RedirectResponse
    {
        $this->autorizarResponsavel($responsavel);
        $dados = $request->validate($this->rules($responsavel), $this->messages());
        DB::transaction(function () use ($dados, $responsavel): void {
            $setores = $dados['setores'];
            unset($dados['setores']);
            $responsavel->update($dados);
            $responsavel->setores()->sync($setores);
        });

        return redirect()->route('responsaveis.show', $responsavel)->with('status', 'Responsável atualizado com sucesso.');
    }

    public function destroy(Responsavel $responsavel): RedirectResponse
    {
        $this->autorizarResponsavel($responsavel);
        $responsavel->delete();
        return redirect()->route('responsaveis.index')->with('status', 'Responsável excluído com sucesso.');
    }

    public function restore(int $responsavel): RedirectResponse
    {
        $registro = Responsavel::onlyTrashed()->findOrFail($responsavel);
        $this->autorizarResponsavel($registro);
        $registro->restore();
        return redirect()->route('responsaveis.index')->with('status', 'Responsável restaurado com sucesso.');
    }

    public function forceDestroy(int $responsavel): RedirectResponse
    {
        $registro = Responsavel::onlyTrashed()->findOrFail($responsavel);
        $this->autorizarResponsavel($registro);
        $registro->forceDelete();
        return redirect()->route('responsaveis.index')->with('status', 'Responsável excluído definitivamente.');
    }

    public function pesquisarColaboradores(Request $request, GiPermissionService $permissoes): JsonResponse
    {
        abort_unless($permissoes->permite('responsaveis.criar', $request) || $permissoes->permite('responsaveis.editar', $request), 403);
        $termo = trim((string) $request->input('q', ''));
        $query = Colaborador::query()->where('ativo', true)->orderBy('nome');
        if ($this->somenteMinhaEquipe()) $query->whereKey($this->colaboradorLogadoId());
        if ($termo !== '') $query->where(fn ($filtro) => $filtro->where('nome', 'like', '%'.$termo.'%')->orWhere('email', 'like', '%'.$termo.'%'));
        return response()->json(['results' => $query->limit(20)->get()->map(fn (Colaborador $colaborador) => ['id' => $colaborador->id, 'text' => $colaborador->nome.' - '.$colaborador->email])]);
    }

    private function somenteMinhaEquipe(): bool
    {
        return app(GiPermissionService::class)->permite('responsaveis.minha_equipe');
    }

    private function colaboradorLogadoId(): int
    {
        $id = filter_var(request()->session()->get('gi_context.usuario.id'), FILTER_VALIDATE_INT);
        abort_unless($id !== false && $id > 0, 403);
        return $id;
    }

    private function autorizarResponsavel(Responsavel $responsavel): void
    {
        if ($this->somenteMinhaEquipe()) {
            abort_unless($responsavel->colaborador_id === $this->colaboradorLogadoId(), 403, 'Você pode acessar apenas seu próprio registro e sua equipe.');
        }
    }

    public function equipe(Request $request, Responsavel $responsavel, DataTableServer $dataTable): View|JsonResponse
    {
        $this->autorizarResponsavel($responsavel);
        if ($request->ajax()) {
            $query = Colaborador::query()->where('responsavel_id', $responsavel->id)->withCount('justificativas');
            // A subconsulta permite pesquisar e ordenar a contagem no DataTable.
            $query = Colaborador::query()->fromSub($query, 'colaboradores')->select('colaboradores.*');
            return $dataTable->response($request, $query, ['id', 'nome', 'justificativas_count', null], fn (Colaborador $colaborador) => [
                'id' => $colaborador->id,
                'nome' => e($colaborador->nome),
                'justificativas_count' => $colaborador->justificativas_count,
                'acoes' => view('components.equipe-actions', ['responsavel' => $responsavel, 'colaborador' => $colaborador])->render(),
            ]);
        }
        $responsavel->load(['colaborador.setor.unidade', 'setores.unidade']);
        return view('responsaveis.equipe', compact('responsavel'));
    }

    public function pesquisarEquipe(Request $request, Responsavel $responsavel): JsonResponse
    {
        $this->autorizarResponsavel($responsavel);
        $termo = trim((string) $request->input('q', ''));
        $query = Colaborador::query()->where('ativo', true)->whereNull('responsavel_id');
        if ($termo !== '') {
            $query->where(fn ($filtro) => $filtro->where('nome', 'like', '%'.$termo.'%')->orWhere('email', 'like', '%'.$termo.'%'));
        }
        return response()->json(['results' => $query->orderBy('nome')->limit(20)->get()->map(fn (Colaborador $colaborador) => [
            'id' => $colaborador->id, 'text' => $colaborador->nome.' - '.$colaborador->email,
        ])]);
    }

    public function adicionarEquipe(Request $request, Responsavel $responsavel): RedirectResponse
    {
        $this->autorizarResponsavel($responsavel);
        $dados = $request->validate(['colaborador_id' => ['required', 'integer', Rule::exists('colaboradores', 'id')->where('ativo', true)->whereNull('responsavel_id')]], [
            'colaborador_id.exists' => 'Selecione um colaborador ativo e sem responsável vinculado.',
        ]);
        $adicionado = Colaborador::query()->whereKey($dados['colaborador_id'])->where('ativo', true)->whereNull('responsavel_id')->update(['responsavel_id' => $responsavel->id]);
        if (! $adicionado) {
            return back()->withErrors(['colaborador_id' => 'Este colaborador não está mais disponível. Atualize a pesquisa.']);
        }
        return redirect()->route('responsaveis.equipe.index', $responsavel)->with('status', 'Colaborador adicionado à equipe com sucesso.');
    }

    public function removerEquipe(Responsavel $responsavel, Colaborador $colaborador): RedirectResponse
    {
        $this->autorizarResponsavel($responsavel);
        $removido = Colaborador::query()->whereKey($colaborador->id)->where('responsavel_id', $responsavel->id)->update(['responsavel_id' => null]);
        abort_unless($removido, 404);
        return redirect()->route('responsaveis.equipe.index', $responsavel)->with('status', 'Colaborador removido da equipe com sucesso.');
    }

    private function form(Responsavel $responsavel, Request $request): View
    {
        $colaboradorId = $request->old('colaborador_id', $responsavel->colaborador_id);
        return view('responsaveis.form', [
            'responsavel' => $responsavel,
            'colaboradorSelecionado' => $colaboradorId ? Colaborador::find($colaboradorId) : null,
            'setores' => Setor::query()->where('ativo', true)->orderBy('nome')->get(),
        ]);
    }

    private function rules(?Responsavel $responsavel = null): array
    {
        return [
            'cargo' => ['required', 'string', 'max:255'],
            'colaborador_id' => ['required', 'integer', Rule::exists('colaboradores', 'id'), ...($this->somenteMinhaEquipe() ? [Rule::in([$this->colaboradorLogadoId()])] : []), Rule::unique('responsaveis', 'colaborador_id')->ignore($responsavel?->id)],
            'setores' => ['required', 'array', 'min:1'],
            'setores.*' => ['integer', Rule::exists('setores', 'id')->where(fn ($query) => $query->where('ativo', true)->whereNull('deleted_at'))],
        ];
    }

    private function messages(): array
    {
        return ['colaborador_id.unique' => 'Já existe um responsável cadastrado para este colaborador. Edite o registro existente para adicionar outros setores ao responsável.'];
    }
}
