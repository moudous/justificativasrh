<?php

use App\Http\Controllers\ColaboradorController;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\Cid10CapituloController;
use App\Http\Controllers\Cid10CategoriaController;
use App\Http\Controllers\Cid10GrupoController;
use App\Http\Controllers\Cid10SubcategoriaController;
use App\Http\Controllers\ResponsavelController;
use App\Http\Controllers\JustificativaController;
use App\Http\Controllers\GrauParentescoController;
use App\Http\Controllers\SetorController;
use App\Http\Controllers\UnidadeController;
use App\Services\GiColaboradorSynchronizer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;

Route::get('/clear-cache', function () {
    $exitCode = Artisan::call('optimize:clear');

    return response()->json([
        'success' => $exitCode === 0,
        'message' => $exitCode === 0
            ? 'Cache limpo com sucesso.'
            : 'Não foi possível limpar o cache.',
        'output' => trim(Artisan::output()),
    ], $exitCode === 0 ? 200 : 500);
})->name('clear-cache');

Route::get('/auth/gi', function (Request $request) {
    abort_unless($request->filled('code'), 400, 'Código ausente.');

    $response = Http::asForm()->timeout(10)->post(
        rtrim(config('gi.gi_url'), '/').'/integracoes/gi/trocar-codigo',
        [
            'client_id' => config('gi.client_id'),
            'client_secret' => config('gi.client_secret'),
            'code' => $request->string('code')->toString(),
        ],
    );

    abort_unless($response->successful(), 401, 'Não foi possível autenticar pelo GI.');

    $contexto = (array) $response->json('data');
    app(GiColaboradorSynchronizer::class)->sync($contexto);

    $request->session()->regenerate();
    $request->session()->put('gi_context', $contexto);

    $destination = (string) $response->json('data.caminho', '/');
    if (! str_starts_with($destination, '/')
        || str_starts_with($destination, '//')
        || str_contains($destination, '\\')
        || str_contains($destination, '..')) {
        $destination = '/';
    }

    return redirect($destination);
})->name('auth.gi');

Route::middleware('gi.session')->prefix('colaboradores')->name('colaboradores.')->group(function (): void {
    Route::get('/', [ColaboradorController::class, 'index'])->middleware('gi.permission:colaboradores.listar')->name('index');
    Route::get('/{colaborador}', [ColaboradorController::class, 'show'])->middleware('gi.permission:colaboradores.visualizar')->name('show');
    Route::get('/{colaborador}/edit', [ColaboradorController::class, 'edit'])->middleware('gi.permission:colaboradores.editar')->name('edit');
    Route::put('/{colaborador}', [ColaboradorController::class, 'update'])->middleware('gi.permission:colaboradores.editar')->name('update');
});

$registrarCadastro = function (string $uri, string $nome, string $controller, ?string $permissao = null): void {
    $permissao ??= $nome;
    Route::middleware('gi.session')->prefix($uri)->name($nome.'.')->group(function () use ($nome, $controller, $permissao): void {
        Route::get('/', [$controller, 'index'])->middleware("gi.permission:$permissao.listar")->name('index');
        Route::get('/criar', [$controller, 'create'])->middleware("gi.permission:$permissao.criar")->name('create');
        Route::post('/', [$controller, 'store'])->middleware("gi.permission:$permissao.criar")->name('store');
        Route::patch('/{registro}/restaurar', [$controller, 'restore'])->middleware("gi.permission:$permissao.restaurar")->name('restore');
        Route::delete('/{registro}/excluir-definitivamente', [$controller, 'forceDestroy'])->middleware("gi.permission:$permissao.excluir_definitivamente")->name('force-destroy');
        Route::patch('/{registro}/status', [$controller, 'toggle'])->middleware("gi.permission:$permissao.editar")->name('toggle');
        Route::get('/{registro}', [$controller, 'show'])->middleware("gi.permission:$permissao.visualizar")->name('show');
        Route::get('/{registro}/editar', [$controller, 'edit'])->middleware("gi.permission:$permissao.editar")->name('edit');
        Route::put('/{registro}', [$controller, 'update'])->middleware("gi.permission:$permissao.editar")->name('update');
        Route::delete('/{registro}', [$controller, 'destroy'])->middleware("gi.permission:$permissao.excluir")->name('destroy');
    });
};

$registrarCadastro('unidades', 'unidades', UnidadeController::class);
$registrarCadastro('setores', 'setores', SetorController::class);
$registrarCadastro('categorias', 'categorias', CategoriaController::class);
$registrarCadastro('parentescos', 'parentescos', GrauParentescoController::class, 'parentesco');

Route::middleware('gi.session')->prefix('cid10_capitulos')->name('cid10_capitulos.')->group(function (): void {
    Route::get('/', [Cid10CapituloController::class, 'index'])->middleware('gi.permission:cid10_capitulos.listar')->name('index');
    Route::get('/criar', [Cid10CapituloController::class, 'create'])->middleware('gi.permission:cid10_capitulos.criar')->name('create');
    Route::post('/', [Cid10CapituloController::class, 'store'])->middleware('gi.permission:cid10_capitulos.criar')->name('store');
    Route::patch('/{capitulo}/restaurar', [Cid10CapituloController::class, 'restore'])->middleware('gi.permission:cid10_capitulos.restaurar')->name('restore');
    Route::patch('/{capitulo}/status', [Cid10CapituloController::class, 'toggle'])->middleware('gi.permission:cid10_capitulos.editar')->name('toggle');
    Route::get('/{capitulo}', [Cid10CapituloController::class, 'show'])->middleware('gi.permission:cid10_capitulos.visualizar')->name('show');
    Route::get('/{capitulo}/editar', [Cid10CapituloController::class, 'edit'])->middleware('gi.permission:cid10_capitulos.editar')->name('edit');
    Route::put('/{capitulo}', [Cid10CapituloController::class, 'update'])->middleware('gi.permission:cid10_capitulos.editar')->name('update');
    Route::delete('/{capitulo}', [Cid10CapituloController::class, 'destroy'])->middleware('gi.permission:cid10_capitulos.excluir')->name('destroy');
});

Route::middleware('gi.session')->prefix('cid10_categorias')->name('cid10_categorias.')->group(function (): void {
    Route::get('/', [Cid10CategoriaController::class, 'index'])->middleware('gi.permission:cid10_categorias.listar')->name('index');
    Route::get('/criar', [Cid10CategoriaController::class, 'create'])->middleware('gi.permission:cid10_categorias.criar')->name('create');
    Route::post('/', [Cid10CategoriaController::class, 'store'])->middleware('gi.permission:cid10_categorias.criar')->name('store');
    Route::patch('/{categoriaCid}/restaurar', [Cid10CategoriaController::class, 'restore'])->middleware('gi.permission:cid10_categorias.restaurar')->name('restore');
    Route::patch('/{categoriaCid}/status', [Cid10CategoriaController::class, 'toggle'])->middleware('gi.permission:cid10_categorias.editar')->name('toggle');
    Route::get('/{categoriaCid}', [Cid10CategoriaController::class, 'show'])->middleware('gi.permission:cid10_categorias.visualizar')->name('show');
    Route::get('/{categoriaCid}/editar', [Cid10CategoriaController::class, 'edit'])->middleware('gi.permission:cid10_categorias.editar')->name('edit');
    Route::put('/{categoriaCid}', [Cid10CategoriaController::class, 'update'])->middleware('gi.permission:cid10_categorias.editar')->name('update');
    Route::delete('/{categoriaCid}', [Cid10CategoriaController::class, 'destroy'])->middleware('gi.permission:cid10_categorias.excluir')->name('destroy');
});

Route::middleware('gi.session')->prefix('cid10_grupos')->name('cid10_grupos.')->group(function (): void {
    Route::get('/', [Cid10GrupoController::class, 'index'])->middleware('gi.permission:cid10_grupos.listar')->name('index');
    Route::get('/criar', [Cid10GrupoController::class, 'create'])->middleware('gi.permission:cid10_grupos.criar')->name('create');
    Route::post('/', [Cid10GrupoController::class, 'store'])->middleware('gi.permission:cid10_grupos.criar')->name('store');
    Route::patch('/{grupo}/restaurar', [Cid10GrupoController::class, 'restore'])->middleware('gi.permission:cid10_grupos.restaurar')->name('restore');
    Route::patch('/{grupo}/status', [Cid10GrupoController::class, 'toggle'])->middleware('gi.permission:cid10_grupos.editar')->name('toggle');
    Route::get('/{grupo}', [Cid10GrupoController::class, 'show'])->middleware('gi.permission:cid10_grupos.visualizar')->name('show');
    Route::get('/{grupo}/editar', [Cid10GrupoController::class, 'edit'])->middleware('gi.permission:cid10_grupos.editar')->name('edit');
    Route::put('/{grupo}', [Cid10GrupoController::class, 'update'])->middleware('gi.permission:cid10_grupos.editar')->name('update');
    Route::delete('/{grupo}', [Cid10GrupoController::class, 'destroy'])->middleware('gi.permission:cid10_grupos.excluir')->name('destroy');
});

Route::middleware('gi.session')->prefix('cid10_subcategorias')->name('cid10_subcategorias.')->group(function (): void {
    Route::get('/', [Cid10SubcategoriaController::class, 'index'])->middleware('gi.permission:cid10_subcategorias.listar')->name('index');
    Route::get('/criar', [Cid10SubcategoriaController::class, 'create'])->middleware('gi.permission:cid10_subcategorias.criar')->name('create');
    Route::post('/', [Cid10SubcategoriaController::class, 'store'])->middleware('gi.permission:cid10_subcategorias.criar')->name('store');
    Route::patch('/{subcategoria}/restaurar', [Cid10SubcategoriaController::class, 'restore'])->middleware('gi.permission:cid10_subcategorias.restaurar')->name('restore');
    Route::patch('/{subcategoria}/status', [Cid10SubcategoriaController::class, 'toggle'])->middleware('gi.permission:cid10_subcategorias.editar')->name('toggle');
    Route::get('/{subcategoria}', [Cid10SubcategoriaController::class, 'show'])->middleware('gi.permission:cid10_subcategorias.visualizar')->name('show');
    Route::get('/{subcategoria}/editar', [Cid10SubcategoriaController::class, 'edit'])->middleware('gi.permission:cid10_subcategorias.editar')->name('edit');
    Route::put('/{subcategoria}', [Cid10SubcategoriaController::class, 'update'])->middleware('gi.permission:cid10_subcategorias.editar')->name('update');
    Route::delete('/{subcategoria}', [Cid10SubcategoriaController::class, 'destroy'])->middleware('gi.permission:cid10_subcategorias.excluir')->name('destroy');
});

Route::middleware('gi.session')->prefix('responsaveis')->name('responsaveis.')->group(function (): void {
    Route::get('/', [ResponsavelController::class, 'index'])->middleware('gi.permission:responsaveis.listar')->name('index');
    Route::get('/colaboradores/pesquisar', [ResponsavelController::class, 'pesquisarColaboradores'])->name('colaboradores.search');
    Route::get('/criar', [ResponsavelController::class, 'create'])->middleware('gi.permission:responsaveis.criar')->name('create');
    Route::post('/', [ResponsavelController::class, 'store'])->middleware('gi.permission:responsaveis.criar')->name('store');
    Route::patch('/{responsavel}/restaurar', [ResponsavelController::class, 'restore'])->middleware('gi.permission:responsaveis.restaurar')->name('restore');
    Route::delete('/{responsavel}/excluir-definitivamente', [ResponsavelController::class, 'forceDestroy'])->middleware('gi.permission:responsaveis.excluir_definitivamente')->name('force-destroy');
    Route::get('/{responsavel}', [ResponsavelController::class, 'show'])->middleware('gi.permission:responsaveis.visualizar')->name('show');
    Route::get('/{responsavel}/editar', [ResponsavelController::class, 'edit'])->middleware('gi.permission:responsaveis.editar')->name('edit');
    Route::put('/{responsavel}', [ResponsavelController::class, 'update'])->middleware('gi.permission:responsaveis.editar')->name('update');
    Route::delete('/{responsavel}', [ResponsavelController::class, 'destroy'])->middleware('gi.permission:responsaveis.excluir')->name('destroy');
});

Route::middleware('gi.session')->prefix('justificativas')->name('justificativas.')->group(function (): void {
    Route::get('/', [JustificativaController::class, 'index'])->middleware('gi.permission:justificativa.listar')->name('index');
    Route::get('/cids/pesquisar', [JustificativaController::class, 'pesquisarCids'])->name('cids.search');
    Route::get('/criar', [JustificativaController::class, 'create'])->middleware('gi.permission:justificativa.criar')->name('create');
    Route::post('/', [JustificativaController::class, 'store'])->middleware('gi.permission:justificativa.criar')->name('store');
    Route::patch('/{justificativa}/restaurar', [JustificativaController::class, 'restore'])->middleware('gi.permission:justificativa.restaurar')->name('restore');
    Route::delete('/{justificativa}/excluir-definitivamente', [JustificativaController::class, 'forceDestroy'])->middleware('gi.permission:justificativa.excluir_definitivamente')->name('force-destroy');
    Route::get('/{justificativa}/historico', [JustificativaController::class, 'historico'])->middleware('gi.permission:justificativa.historico')->name('historico');
    Route::get('/{justificativa}/anexos/{anexo}', [JustificativaController::class, 'anexo'])->middleware('gi.permission:justificativa.anexos.visualizar')->name('anexo');
    Route::delete('/{justificativa}/anexos/{anexo}', [JustificativaController::class, 'destroyAnexo'])->middleware('gi.permission:justificativa.anexos.excluir')->name('anexos.destroy');
    Route::get('/{justificativa}/editar', [JustificativaController::class, 'edit'])->middleware('gi.permission:justificativa.editar')->name('edit');
    Route::put('/{justificativa}', [JustificativaController::class, 'update'])->middleware('gi.permission:justificativa.editar')->name('update');
    Route::get('/{justificativa}', [JustificativaController::class, 'show'])->middleware('gi.permission:justificativa.visualizar')->name('show');
    Route::delete('/{justificativa}', [JustificativaController::class, 'destroy'])->middleware('gi.permission:justificativa.excluir')->name('destroy');
    Route::patch('/{justificativa}/controle', [JustificativaController::class, 'alterarControle'])->middleware('gi.permission:justificativa.listar')->name('controle');
});

Route::middleware(['gi.session', 'gi.permission:justificativa.listar'])->group(function (): void {
    Route::get('/justificativa_gestao', [JustificativaController::class, 'gestao'])->name('justificativas.gestao');
    Route::get('/justificativa_rh', [JustificativaController::class, 'rh'])->name('justificativas.rh');
    Route::get('/justificativas_finalizadas', [JustificativaController::class, 'finalizadas'])->name('justificativas.finalizadas');
});

Route::get('/', function (Request $request) {
    abort_unless($request->session()->has('gi_context'), 401, 'Abra esta aplicação pelo menu do GI.');

    $visibleContext = $request->session()->get('gi_context');
    unset($visibleContext['access_token']);

    return response()
        ->view('session', ['context' => $visibleContext])
        ->header('Cache-Control', 'no-store');
});

Route::get('/gi/{resource}', function (Request $request, string $resource) {
    abort_unless($request->session()->has('gi_context'), 401);
    abort_unless(in_array($resource, ['perfis', 'usuarios'], true), 404);

    $upstreamResponse = Http::withToken($request->session()->get('gi_context.access_token'))
        ->acceptJson()->timeout(10)
        ->get(rtrim(config('gi.gi_url'), '/').'/api/integracoes/v1/'.$resource);

    return response($upstreamResponse->body(), $upstreamResponse->status())
        ->header(
            'Content-Type',
            $upstreamResponse->header('Content-Type') ?? 'application/json',
        );
});
