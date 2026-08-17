<?php

namespace App\Http\Controllers;

use App\Models\Categoria;

class CategoriaController extends CadastroController
{
    protected string $modelClass = Categoria::class;
    protected string $recurso = 'categorias';
    protected string $singular = 'categoria';
    protected string $plural = 'categorias';
}
