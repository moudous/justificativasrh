<?php

namespace App\Http\Controllers;

use App\Models\Colaborador;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ColaboradorController extends Controller
{
    public function index(): View
    {
        return view('colaboradores.index', [
            'colaboradores' => Colaborador::query()->orderBy('nome')->get(),
        ]);
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
