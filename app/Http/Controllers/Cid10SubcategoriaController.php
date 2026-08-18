<?php

namespace App\Http\Controllers;

use App\Models\Cid10Subcategoria;
use App\Services\DataTableServer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class Cid10SubcategoriaController extends Controller
{
    public function index(Request $request, DataTableServer $dataTable): View|JsonResponse
    {
        if ($request->ajax()) {
            return $dataTable->response($request, Cid10Subcategoria::withTrashed(), ['id', 'subcat', 'classif', 'descricao', 'descrabrev', 'ativo', null], fn ($registro) => [
                'id' => $registro->id, 'subcat' => $registro->subcat, 'classif' => $registro->classif,
                'descricao' => e($registro->descricao).($registro->trashed() ? ' <span class="badge text-bg-danger">Excluída</span>' : ''),
                'descrabrev' => $registro->descrabrev, 'situacao' => view('components.situacao', compact('registro'))->render(),
                'acoes' => view('components.cid10-actions', ['registro' => $registro, 'rota' => 'cid10_subcategorias', 'permissao' => 'cid10_subcategorias'])->render(),
            ]);
        }
        return view('cid10_subcategorias.index');
    }

    public function create(): View
    {
        return view('cid10_subcategorias.form', ['subcategoria' => new Cid10Subcategoria]);
    }

    public function store(Request $request): RedirectResponse
    {
        $subcategoria = Cid10Subcategoria::create($request->validate($this->rules()));

        return redirect()->route('cid10_subcategorias.show', $subcategoria)->with('status', 'Subcategoria CID-10 cadastrada com sucesso.');
    }

    public function show(Cid10Subcategoria $subcategoria): View
    {
        return view('cid10_subcategorias.show', compact('subcategoria'));
    }

    public function edit(Cid10Subcategoria $subcategoria): View
    {
        return view('cid10_subcategorias.form', compact('subcategoria'));
    }

    public function update(Request $request, Cid10Subcategoria $subcategoria): RedirectResponse
    {
        $subcategoria->update($request->validate($this->rules()));

        return redirect()->route('cid10_subcategorias.show', $subcategoria)->with('status', 'Subcategoria CID-10 atualizada com sucesso.');
    }

    public function toggle(Cid10Subcategoria $subcategoria): RedirectResponse
    {
        $subcategoria->update(['ativo' => ! $subcategoria->ativo]);

        return back()->with('status', 'Situação atualizada com sucesso.');
    }

    public function destroy(Cid10Subcategoria $subcategoria): RedirectResponse
    {
        $subcategoria->delete();

        return redirect()->route('cid10_subcategorias.index')->with('status', 'Subcategoria CID-10 excluída com sucesso.');
    }

    public function restore(int $subcategoria): RedirectResponse
    {
        Cid10Subcategoria::onlyTrashed()->findOrFail($subcategoria)->restore();

        return redirect()->route('cid10_subcategorias.index')->with('status', 'Subcategoria CID-10 restaurada com sucesso.');
    }

    private function rules(): array
    {
        return [
            'ativo' => ['required', 'boolean'],
            'subcat' => ['nullable', 'string', 'max:4'],
            'classif' => ['nullable', 'string', 'max:255'],
            'restrsexo' => ['nullable', 'string', 'max:255'],
            'causaobito' => ['nullable', 'string', 'max:255'],
            'descricao' => ['nullable', 'string', 'max:262'],
            'descrabrev' => ['nullable', 'string', 'max:255'],
            'refer' => ['nullable', 'string', 'max:255'],
            'excluidos' => ['nullable', 'string', 'max:255'],
        ];
    }
}
