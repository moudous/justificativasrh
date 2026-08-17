<?php

namespace App\Http\Controllers;

use App\Models\GrauParentesco;

class GrauParentescoController extends CadastroController
{
    protected string $modelClass = GrauParentesco::class;
    protected string $recurso = 'parentescos';
    protected string $permissao = 'parentesco';
    protected string $singular = 'grau de parentesco';
    protected string $plural = 'graus de parentesco';
}
