<?php

namespace App\Http\Controllers;

use App\Models\Unidade;

class UnidadeController extends CadastroController
{
    protected string $modelClass = Unidade::class;
    protected string $recurso = 'unidades';
    protected string $singular = 'unidade';
    protected string $plural = 'unidades';
}
