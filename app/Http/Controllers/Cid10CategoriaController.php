<?php

namespace App\Http\Controllers;

use App\Models\Cid10Categoria;
use App\Services\DataTableServer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class Cid10CategoriaController extends Controller
{
    public function index(Request $request, DataTableServer $dataTable): View|JsonResponse
    {
        if ($request->ajax()) {
            return $dataTable->response($request, Cid10Categoria::withTrashed(), ['id', 'cat', 'classif', 'descricao', 'descrabrev', 'refer', 'excluidos', 'ativo', null], fn ($registro) => [
                'id' => $registro->id, 'cat' => $registro->cat, 'classif' => $registro->classif,
                'descricao' => e($registro->descricao).($registro->trashed() ? ' <span class="badge text-bg-danger">Excluída</span>' : ''),
                'descrabrev' => $registro->descrabrev, 'refer' => $registro->refer, 'excluidos' => $registro->excluidos,
                'situacao' => view('components.situacao', compact('registro'))->render(),
                'acoes' => view('components.cid10-actions', ['registro' => $registro, 'rota' => 'cid10_categorias', 'permissao' => 'cid10_categorias'])->render(),
            ]);
        }
        return view('cid10_categorias.index');
    }

    public function create(): View
    {
        return view('cid10_categorias.form', ['categoriaCid' => new Cid10Categoria]);
    }

    public function store(Request $request): RedirectResponse
    {
        $categoriaCid = Cid10Categoria::create($request->validate($this->rules()));

        return redirect()->route('cid10_categorias.show', $categoriaCid)->with('status', 'Categoria CID-10 cadastrada com sucesso.');
    }

    public function show(Cid10Categoria $categoriaCid): View
    {
        return view('cid10_categorias.show', compact('categoriaCid'));
    }

    public function edit(Cid10Categoria $categoriaCid): View
    {
        return view('cid10_categorias.form', compact('categoriaCid'));
    }

    public function update(Request $request, Cid10Categoria $categoriaCid): RedirectResponse
    {
        $categoriaCid->update($request->validate($this->rules()));

        return redirect()->route('cid10_categorias.show', $categoriaCid)->with('status', 'Categoria CID-10 atualizada com sucesso.');
    }

    public function toggle(Cid10Categoria $categoriaCid): RedirectResponse
    {
        $categoriaCid->update(['ativo' => ! $categoriaCid->ativo]);

        return back()->with('status', 'Situação atualizada com sucesso.');
    }

    public function destroy(Cid10Categoria $categoriaCid): RedirectResponse
    {
        $categoriaCid->delete();

        return redirect()->route('cid10_categorias.index')->with('status', 'Categoria CID-10 excluída com sucesso.');
    }

    public function restore(int $categoriaCid): RedirectResponse
    {
        Cid10Categoria::onlyTrashed()->findOrFail($categoriaCid)->restore();

        return redirect()->route('cid10_categorias.index')->with('status', 'Categoria CID-10 restaurada com sucesso.');
    }

    private function rules(): array
    {
        return [
            'ativo' => ['required', 'boolean'],
            'cat' => ['nullable', 'string', 'max:3'],
            'classif' => ['nullable', 'string', 'max:255'],
            'descricao' => ['nullable', 'string', 'max:255'],
            'descrabrev' => ['nullable', 'string', 'max:255'],
            'refer' => ['nullable', 'string', 'max:255'],
            'excluidos' => ['nullable', 'string', 'max:255'],
        ];
    }
}
