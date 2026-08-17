<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\Colaborador;
use App\Models\GrauParentesco;
use App\Models\Justificativa;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class JustificativaController extends Controller
{
    public function index(): View
    {
        return view('justificativas.index', [
            'justificativas' => Justificativa::query()
                ->with(['colaborador', 'categoria'])
                ->withTrashed()
                ->latest()
                ->get(),
        ]);
    }

    public function create(Request $request): View
    {
        return view('justificativas.form', [
            'colaborador' => $this->colaboradorLogado($request),
            'categorias' => Categoria::query()->where('ativo', true)->orderBy('nome')->get(),
            'grausParentesco' => GrauParentesco::query()->where('ativo', true)->orderBy('nome')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $dados = $request->validate($this->rules());
        $dados['colaborador_id'] = $this->colaboradorLogado($request)->id;
        $dados['status'] = 'Pendente';
        $dados['tipo_atestado'] = $dados['atestado_medico'] ? ($dados['tipo_atestado'] ?? null) : null;
        $dados['grau_parentesco_id'] = $dados['tipo_atestado'] === 'acompanhamento' ? ($dados['grau_parentesco_id'] ?? null) : null;

        $anexo = $request->file('anexo');
        $dados['anexo_caminho'] = $anexo->store('justificativas', 'local');
        $dados['anexo_nome_original'] = $anexo->getClientOriginalName();
        $dados['anexo_mime'] = $anexo->getMimeType();
        unset($dados['anexo']);

        try {
            $justificativa = Justificativa::query()->create($dados);
        } catch (\Throwable $erro) {
            Storage::disk('local')->delete($dados['anexo_caminho']);
            throw $erro;
        }

        return redirect()->route('justificativas.show', $justificativa)
            ->with('status', 'Justificativa cadastrada com sucesso.');
    }

    public function show(int $justificativa): View
    {
        return view('justificativas.show', [
            'justificativa' => Justificativa::query()
                ->with(['colaborador', 'categoria', 'grauParentesco'])
                ->findOrFail($justificativa),
        ]);
    }

    public function destroy(int $justificativa): RedirectResponse
    {
        Justificativa::query()->findOrFail($justificativa)->delete();

        return redirect()->route('justificativas.index')
            ->with('status', 'Justificativa excluída com sucesso.');
    }

    public function restore(int $justificativa): RedirectResponse
    {
        Justificativa::onlyTrashed()->findOrFail($justificativa)->restore();

        return redirect()->route('justificativas.index')
            ->with('status', 'Justificativa restaurada com sucesso.');
    }

    public function forceDestroy(int $justificativa): RedirectResponse
    {
        Justificativa::onlyTrashed()->findOrFail($justificativa)->forceDelete();

        return redirect()->route('justificativas.index')
            ->with('status', 'Justificativa excluída definitivamente.');
    }

    public function historico(int $justificativa): View
    {
        $justificativa = Justificativa::withTrashed()
            ->with(['historicos' => fn ($query) => $query->latest()])
            ->findOrFail($justificativa);

        return view('justificativas.historico', compact('justificativa'));
    }

    public function anexo(int $justificativa): BinaryFileResponse
    {
        $justificativa = Justificativa::withTrashed()->findOrFail($justificativa);
        abort_unless($justificativa->anexo_caminho && Storage::disk('local')->exists($justificativa->anexo_caminho), 404);

        return response()->file(Storage::disk('local')->path($justificativa->anexo_caminho), [
            'Content-Type' => $justificativa->anexo_mime,
        ]);
    }

    private function rules(): array
    {
        return [
            'descricao' => ['required', 'string'],
            'categoria_id' => ['required', 'integer', Rule::exists('categorias', 'id')->whereNull('deleted_at')],
            'anexo' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:10240'],
            'atestado_medico' => ['required', 'boolean'],
            'tipo_atestado' => [
                'nullable',
                Rule::requiredIf(fn (): bool => request()->boolean('atestado_medico')),
                Rule::in(['proprio', 'acompanhamento']),
            ],
            'grau_parentesco_id' => [
                'nullable',
                Rule::requiredIf(fn (): bool => request()->boolean('atestado_medico') && request('tipo_atestado') === 'acompanhamento'),
                'integer',
                Rule::exists('graus_parentescos', 'id')->where(fn ($query) => $query->where('ativo', true)->whereNull('deleted_at')),
            ],
        ];
    }

    private function colaboradorLogado(Request $request): Colaborador
    {
        $id = filter_var($request->session()->get('gi_context.usuario.id'), FILTER_VALIDATE_INT);

        abort_if($id === false || $id < 1, 401, 'O GI não informou o usuário autenticado.');

        return Colaborador::query()->findOrFail($id);
    }
}
