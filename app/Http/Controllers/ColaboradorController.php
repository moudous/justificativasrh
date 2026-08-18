<?php

namespace App\Http\Controllers;

use App\Models\Colaborador;
use App\Models\Responsavel;
use App\Models\Setor;
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
        return view('colaboradores.form', [
            'colaborador' => $colaborador->load(['setor', 'responsavel.colaborador']),
            'setores' => Setor::query()->where('ativo', true)->orderBy('nome')->get(),
            'responsaveis' => Responsavel::query()
                ->with(['colaborador', 'setores' => fn ($query) => $query->where('setores.ativo', true)])
                ->whereHas('colaborador', fn ($query) => $query->where('ativo', true))
                ->orderBy('id')
                ->get(),
        ]);
    }

    public function update(Request $request, Colaborador $colaborador): RedirectResponse
    {
        $regras = [
            'ativo' => ['required', 'boolean'],
            'setor_id' => ['nullable', 'integer', Rule::exists('setores', 'id')->where(fn ($query) => $query->where('ativo', true)->whereNull('deleted_at'))],
            'responsavel_id' => ['nullable', 'integer', Rule::exists('responsaveis', 'id')->where(fn ($query) => $query->whereNull('deleted_at'))],
        ];

        $dados = $request->validate($regras);

        if (empty($dados['setor_id']) && ! empty($dados['responsavel_id'])) {
            $setoresDoResponsavel = Responsavel::query()
                ->findOrFail($dados['responsavel_id'])
                ->setores()
                ->where('setores.ativo', true)
                ->limit(2)
                ->pluck('setores.id');

            if ($setoresDoResponsavel->count() === 1) {
                $dados['setor_id'] = $setoresDoResponsavel->first();
            }
        }

        $colaborador->update($dados);

        return redirect()->route('colaboradores.show', $colaborador)
            ->with('status', 'Colaborador atualizado com sucesso.');
    }
}
