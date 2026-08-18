<?php

namespace App\Http\Controllers;

use App\Models\Cid10Capitulo;
use App\Services\DataTableServer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class Cid10CapituloController extends Controller
{
    public function index(Request $request, DataTableServer $dataTable): View|JsonResponse
    {
        if ($request->ajax()) {
            return $dataTable->response($request, Cid10Capitulo::withTrashed(), ['id', 'numcap', 'catinic', 'catfim', 'descricao', 'descrabrev', 'ativo', null], fn ($registro) => [
                'id' => $registro->id, 'numcap' => $registro->numcap, 'catinic' => $registro->catinic, 'catfim' => $registro->catfim,
                'descricao' => e($registro->descricao).($registro->trashed() ? ' <span class="badge text-bg-danger">Excluído</span>' : ''),
                'descrabrev' => $registro->descrabrev, 'situacao' => view('components.situacao', compact('registro'))->render(),
                'acoes' => view('components.cid10-actions', ['registro' => $registro, 'rota' => 'cid10_capitulos', 'permissao' => 'cid10_capitulos'])->render(),
            ]);
        }
        return view('cid10_capitulos.index');
    }

    public function create(): View
    {
        return view('cid10_capitulos.form', ['capitulo' => new Cid10Capitulo]);
    }

    public function store(Request $request): RedirectResponse
    {
        $capitulo = Cid10Capitulo::create($request->validate($this->rules()));

        return redirect()->route('cid10_capitulos.show', $capitulo)
            ->with('status', 'Capítulo CID-10 cadastrado com sucesso.');
    }

    public function show(Cid10Capitulo $capitulo): View
    {
        return view('cid10_capitulos.show', compact('capitulo'));
    }

    public function edit(Cid10Capitulo $capitulo): View
    {
        return view('cid10_capitulos.form', compact('capitulo'));
    }

    public function update(Request $request, Cid10Capitulo $capitulo): RedirectResponse
    {
        $capitulo->update($request->validate($this->rules()));

        return redirect()->route('cid10_capitulos.show', $capitulo)
            ->with('status', 'Capítulo CID-10 atualizado com sucesso.');
    }

    public function toggle(Cid10Capitulo $capitulo): RedirectResponse
    {
        $capitulo->update(['ativo' => ! $capitulo->ativo]);

        return back()->with('status', 'Situação atualizada com sucesso.');
    }

    public function destroy(Cid10Capitulo $capitulo): RedirectResponse
    {
        $capitulo->delete();

        return redirect()->route('cid10_capitulos.index')
            ->with('status', 'Capítulo CID-10 excluído com sucesso.');
    }

    public function restore(int $capitulo): RedirectResponse
    {
        Cid10Capitulo::onlyTrashed()->findOrFail($capitulo)->restore();

        return redirect()->route('cid10_capitulos.index')
            ->with('status', 'Capítulo CID-10 restaurado com sucesso.');
    }

    private function rules(): array
    {
        return [
            'ativo' => ['required', 'boolean'],
            'numcap' => ['nullable', 'integer'],
            'catinic' => ['nullable', 'string', 'max:255'],
            'catfim' => ['nullable', 'string', 'max:255'],
            'descricao' => ['nullable', 'string', 'max:255'],
            'descrabrev' => ['nullable', 'string', 'max:255'],
        ];
    }
}
