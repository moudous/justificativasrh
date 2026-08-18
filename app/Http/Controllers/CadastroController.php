<?php

namespace App\Http\Controllers;

use App\Services\DataTableServer;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

abstract class CadastroController extends Controller
{
    protected string $modelClass;
    protected string $recurso;
    protected string $permissao = '';
    protected string $singular;
    protected string $plural;

    public function index(Request $request, DataTableServer $dataTable): View|JsonResponse
    {
        if ($request->ajax()) {
            $temUnidade = $this instanceof SetorController;
            $columns = $temUnidade ? ['id', 'nome', null, 'ativo', 'updated_at', null] : ['id', 'nome', 'ativo', 'updated_at', null];
            return $dataTable->response($request, $this->indexQuery()->withTrashed(), $columns, function ($registro) use ($temUnidade): array {
                $data = ['id' => $registro->id, 'nome' => e($registro->nome).($registro->trashed() ? ' <span class="badge text-bg-danger">Excluído</span>' : '')];
                if ($temUnidade) $data['unidade'] = $registro->unidade?->nome ?? '—';
                $data['situacao'] = view('components.situacao', compact('registro'))->render();
                $data['atualizado_em'] = $registro->updated_at?->format('d/m/Y H:i') ?? '—';
                $data['acoes'] = view('components.cadastro-actions', [
                    'registro' => $registro,
                    'recurso' => $this->recurso,
                    'permissao' => $this->permissao ?: $this->recurso,
                ])->render();
                return $data;
            });
        }
        return view('cadastros.index', $this->viewData());
    }

    public function create(): View
    {
        return view('cadastros.form', $this->viewData([
            'registro' => new $this->modelClass,
        ]));
    }

    public function store(Request $request): RedirectResponse
    {
        $registro = $this->modelClass::query()->create($request->validate($this->rules()));

        return redirect()->route($this->recurso.'.show', $registro)
            ->with('status', ucfirst($this->singular).' cadastrado(a) com sucesso.');
    }

    public function show(int $registro): View
    {
        $registro = $this->modelClass::query()->findOrFail($registro);
        return view('cadastros.show', $this->viewData(compact('registro')));
    }

    public function edit(int $registro): View
    {
        $registro = $this->modelClass::query()->findOrFail($registro);
        return view('cadastros.form', $this->viewData(compact('registro')));
    }

    public function update(Request $request, int $registro): RedirectResponse
    {
        $registro = $this->modelClass::query()->findOrFail($registro);
        $registro->update($request->validate($this->rules($registro)));

        return redirect()->route($this->recurso.'.show', $registro)
            ->with('status', ucfirst($this->singular).' atualizado(a) com sucesso.');
    }

    public function destroy(int $registro): RedirectResponse
    {
        $registro = $this->modelClass::query()->findOrFail($registro);
        $registro->delete();

        return redirect()->route($this->recurso.'.index')
            ->with('status', ucfirst($this->singular).' excluído(a) com sucesso.');
    }

    public function restore(int $id): RedirectResponse
    {
        $registro = $this->modelClass::onlyTrashed()->findOrFail($id);
        $registro->restore();

        return redirect()->route($this->recurso.'.index')
            ->with('status', ucfirst($this->singular).' restaurado(a) com sucesso.');
    }

    public function forceDestroy(int $id): RedirectResponse
    {
        $registro = $this->modelClass::onlyTrashed()->findOrFail($id);
        $registro->forceDelete();

        return redirect()->route($this->recurso.'.index')
            ->with('status', ucfirst($this->singular).' excluído(a) definitivamente.');
    }

    public function toggle(int $registro): RedirectResponse
    {
        $registro = $this->modelClass::query()->findOrFail($registro);
        $registro->update(['ativo' => ! $registro->ativo]);

        return redirect()->route($this->recurso.'.index')
            ->with('status', 'Status atualizado com sucesso.');
    }

    protected function indexQuery(): Builder
    {
        return $this->modelClass::query();
    }

    protected function rules(?Model $registro = null): array
    {
        return [
            'nome' => ['required', 'string', 'max:255'],
            'ativo' => ['required', 'boolean'],
        ];
    }

    protected function viewData(array $data = []): array
    {
        return array_merge([
            'recurso' => $this->recurso,
            'permissao' => $this->permissao ?: $this->recurso,
            'singular' => $this->singular,
            'plural' => $this->plural,
            'temUnidade' => false,
        ], $data);
    }
}
