<?php

use App\Http\Controllers\ColaboradorController;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\SetorController;
use App\Http\Controllers\UnidadeController;
use App\Services\GiColaboradorSynchronizer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;

Route::get('/auth/gi', function (Request $request) {
    abort_unless($request->filled('code'), 400, 'Código ausente.');

    $response = Http::asForm()->timeout(10)->post(
        rtrim(env('GI_URL'), '/').'/integracoes/gi/trocar-codigo',
        [
            'client_id' => env('GI_CLIENT_ID'),
            'client_secret' => env('GI_CLIENT_SECRET'),
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
});

Route::middleware('gi.session')->prefix('colaboradores')->name('colaboradores.')->group(function (): void {
    Route::get('/', [ColaboradorController::class, 'index'])->middleware('gi.permission:colaboradores.listar')->name('index');
    Route::get('/{colaborador}', [ColaboradorController::class, 'show'])->middleware('gi.permission:colaboradores.visualizar')->name('show');
    Route::get('/{colaborador}/edit', [ColaboradorController::class, 'edit'])->middleware('gi.permission:colaboradores.editar')->name('edit');
    Route::put('/{colaborador}', [ColaboradorController::class, 'update'])->middleware('gi.permission:colaboradores.editar')->name('update');
});

$registrarCadastro = function (string $uri, string $nome, string $controller): void {
    Route::middleware('gi.session')->prefix($uri)->name($nome.'.')->group(function () use ($nome, $controller): void {
        Route::get('/', [$controller, 'index'])->middleware("gi.permission:$nome.listar")->name('index');
        Route::get('/criar', [$controller, 'create'])->middleware("gi.permission:$nome.criar")->name('create');
        Route::post('/', [$controller, 'store'])->middleware("gi.permission:$nome.criar")->name('store');
        Route::patch('/{registro}/restaurar', [$controller, 'restore'])->middleware("gi.permission:$nome.restaurar")->name('restore');
        Route::delete('/{registro}/excluir-definitivamente', [$controller, 'forceDestroy'])->middleware("gi.permission:$nome.excluir_definitivamente")->name('force-destroy');
        Route::patch('/{registro}/status', [$controller, 'toggle'])->middleware("gi.permission:$nome.editar")->name('toggle');
        Route::get('/{registro}', [$controller, 'show'])->middleware("gi.permission:$nome.visualizar")->name('show');
        Route::get('/{registro}/editar', [$controller, 'edit'])->middleware("gi.permission:$nome.editar")->name('edit');
        Route::put('/{registro}', [$controller, 'update'])->middleware("gi.permission:$nome.editar")->name('update');
        Route::delete('/{registro}', [$controller, 'destroy'])->middleware("gi.permission:$nome.excluir")->name('destroy');
    });
};

$registrarCadastro('unidades', 'unidades', UnidadeController::class);
$registrarCadastro('setores', 'setores', SetorController::class);
$registrarCadastro('categorias', 'categorias', CategoriaController::class);

Route::get('/', function (Request $request) {
    abort_unless($request->session()->has('gi_context'), 401, 'Abra esta aplicação pelo menu do GI.');

    $visibleContext = $request->session()->get('gi_context');
    unset($visibleContext['access_token']);

    return response()
        ->view('session', ['context' => $visibleContext])
        ->header('Content-Security-Policy', "frame-ancestors ".env('GI_FRAME_ANCESTORS')."; object-src 'none'; base-uri 'self'")
        ->header('Cache-Control', 'no-store');
});

Route::get('/gi/{resource}', function (Request $request, string $resource) {
    abort_unless($request->session()->has('gi_context'), 401);
    abort_unless(in_array($resource, ['perfis', 'usuarios'], true), 404);

    $upstreamResponse = Http::withToken($request->session()->get('gi_context.access_token'))
        ->acceptJson()->timeout(10)
        ->get(rtrim(env('GI_URL'), '/').'/api/integracoes/v1/'.$resource);

    return response($upstreamResponse->body(), $upstreamResponse->status())
        ->header(
            'Content-Type',
            $upstreamResponse->header('Content-Type') ?? 'application/json',
        );
});
