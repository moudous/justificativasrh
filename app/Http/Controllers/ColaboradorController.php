<?php

namespace App\Http\Controllers;

use App\Models\Colaborador;
use App\Services\DataTableServer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ColaboradorController extends Controller
{
    public function index(Request $request, DataTableServer $dataTable): View|JsonResponse
    {
        if ($request->ajax()) {
            return $dataTable->response($request, Colaborador::query(), ['id', 'nome', 'email', 'perfil', 'perfil_id', 'ativo', 'updated_at', null], fn ($registro) => [
                'id' => $registro->id, 'nome' => $registro->nome, 'email' => $registro->email, 'perfil' => $registro->perfil, 'perfil_id' => $registro->perfil_id,
                'situacao' => view('components.situacao', compact('registro'))->render(), 'atualizado_em' => $registro->updated_at?->format('d/m/Y H:i') ?? '—',
                'acoes' => view('components.colaborador-actions', ['colaborador' => $registro])->render(),
            ]);
        }
        return view('colaboradores.index');
    }

    public function show(Colaborador $colaborador): View
    {
        return view('colaboradores.show', compact('colaborador'));
    }

    public function edit(Colaborador $colaborador): View
    {
        return view('colaboradores.form', compact('colaborador'));
    }

    public function update(Request $request, Colaborador $colaborador): RedirectResponse
    {
        $dados = $request->validate([
            'nome' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('colaboradores')->ignore($colaborador->id)],
            'perfil' => ['required', 'string', 'max:255'],
            'perfil_id' => ['required', 'integer', 'min:1'],
            'ativo' => ['required', 'boolean'],
        ]);

        $colaborador->update($dados);

        return redirect()->route('colaboradores.show', $colaborador)
            ->with('status', 'Colaborador atualizado com sucesso.');
    }
}
