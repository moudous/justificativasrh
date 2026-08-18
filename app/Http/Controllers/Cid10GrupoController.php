<?php

namespace App\Http\Controllers;

use App\Models\Cid10Grupo;
use App\Services\DataTableServer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class Cid10GrupoController extends Controller
{
    public function index(Request $request, DataTableServer $dataTable): View|JsonResponse
    {
        if ($request->ajax()) {
            return $dataTable->response($request, Cid10Grupo::withTrashed(), ['id', 'cod_grupo', 'titulo', 'catinic', 'catfim', 'descricao', 'descrabrev', 'ativo', null], fn ($registro) => [
                'id' => $registro->id, 'cod_grupo' => $registro->cod_grupo,
                'titulo' => e($registro->titulo).($registro->trashed() ? ' <span class="badge text-bg-danger">Excluído</span>' : ''),
                'catinic' => $registro->catinic, 'catfim' => $registro->catfim, 'descricao' => $registro->descricao, 'descrabrev' => $registro->descrabrev,
                'situacao' => view('components.situacao', compact('registro'))->render(),
                'acoes' => view('components.cid10-actions', ['registro' => $registro, 'rota' => 'cid10_grupos', 'permissao' => 'cid10_grupos'])->render(),
            ]);
        }
        return view('cid10_grupos.index');
    }

    public function create(): View
    {
        return view('cid10_grupos.form', ['grupo' => new Cid10Grupo]);
    }

    public function store(Request $request): RedirectResponse
    {
        $grupo = Cid10Grupo::create($request->validate($this->rules()));

        return redirect()->route('cid10_grupos.show', $grupo)->with('status', 'Grupo CID-10 cadastrado com sucesso.');
    }

    public function show(Cid10Grupo $grupo): View
    {
        return view('cid10_grupos.show', compact('grupo'));
    }

    public function edit(Cid10Grupo $grupo): View
    {
        return view('cid10_grupos.form', compact('grupo'));
    }

    public function update(Request $request, Cid10Grupo $grupo): RedirectResponse
    {
        $grupo->update($request->validate($this->rules()));

        return redirect()->route('cid10_grupos.show', $grupo)->with('status', 'Grupo CID-10 atualizado com sucesso.');
    }

    public function toggle(Cid10Grupo $grupo): RedirectResponse
    {
        $grupo->update(['ativo' => ! $grupo->ativo]);

        return back()->with('status', 'Situação atualizada com sucesso.');
    }

    public function destroy(Cid10Grupo $grupo): RedirectResponse
    {
        $grupo->delete();

        return redirect()->route('cid10_grupos.index')->with('status', 'Grupo CID-10 excluído com sucesso.');
    }

    public function restore(int $grupo): RedirectResponse
    {
        Cid10Grupo::onlyTrashed()->findOrFail($grupo)->restore();

        return redirect()->route('cid10_grupos.index')->with('status', 'Grupo CID-10 restaurado com sucesso.');
    }

    private function rules(): array
    {
        return [
            'titulo' => ['required', 'string'],
            'ativo' => ['required', 'boolean'],
            'cod_grupo' => ['nullable', 'integer'],
            'catinic' => ['nullable', 'string', 'max:255'],
            'catfim' => ['nullable', 'string', 'max:255'],
            'descricao' => ['nullable', 'string', 'max:255'],
            'descrabrev' => ['nullable', 'string', 'max:255'],
        ];
    }
}
