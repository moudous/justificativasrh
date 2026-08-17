<?php

namespace App\Http\Controllers;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
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

    public function index(): View
    {
        return view('cadastros.index', $this->viewData([
            'registros' => $this->indexQuery()->withTrashed()->orderBy('nome')->get(),
        ]));
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
