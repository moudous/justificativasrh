<?php

namespace App\Http\Controllers;

use App\Models\Setor;
use App\Models\Unidade;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;

class SetorController extends CadastroController
{
    protected string $modelClass = Setor::class;
    protected string $recurso = 'setores';
    protected string $singular = 'setor';
    protected string $plural = 'setores';

    protected function indexQuery(): Builder
    {
        return Setor::query()->with('unidade');
    }

    protected function rules(?Model $registro = null): array
    {
        return [
            'nome' => ['required', 'string', 'max:255'],
            'unidade_id' => ['required', 'integer', Rule::exists('unidades', 'id')->whereNull('deleted_at')],
            'ativo' => ['required', 'boolean'],
        ];
    }

    protected function viewData(array $data = []): array
    {
        return parent::viewData(array_merge([
            'temUnidade' => true,
            'unidades' => Unidade::query()->orderBy('nome')->get(),
        ], $data));
    }
}
