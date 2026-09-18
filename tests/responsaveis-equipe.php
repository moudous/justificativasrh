<?php

// Execute com: php tests/responsaveis-equipe.php
require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:', 'session.driver' => 'array']);
Illuminate\Support\Facades\DB::purge('sqlite');
Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
$app->make(Illuminate\Contracts\Http\Kernel::class);
$router = app('router');
// Sessão em memória; preserva bindings e os middlewares reais de autorização.
$router->middlewareGroup('web', [Illuminate\Routing\Middleware\SubstituteBindings::class]);
Illuminate\Support\Facades\View::share('errors', new Illuminate\Support\ViewErrorBag);
$session = app('session')->driver();
$session->start();

function check(bool $condition, string $message): void {
    if (! $condition) { fwrite(STDERR, "FALHOU: $message\n"); exit(1); }
    echo "OK: $message\n";
}
function callRoute(string $method, string $uri, array $permissions, int $user = 1, array $data = [], bool $ajax = false): Symfony\Component\HttpFoundation\Response {
    global $session, $router, $app;
    $session->put('gi_context', ['usuario' => ['id' => $user], 'permissoes' => $permissions]);
    $request = Illuminate\Http\Request::create($uri, $method, $data);
    $request->setLaravelSession($session);
    $request->headers->set('Accept', 'application/json');
    if ($ajax) $request->headers->set('X-Requested-With', 'XMLHttpRequest');
    $app->instance('request', $request);
    try { return $router->dispatch($request); }
    catch (Symfony\Component\HttpKernel\Exception\HttpExceptionInterface $exception) { return response('', $exception->getStatusCode()); }
    catch (Illuminate\Validation\ValidationException $exception) { return response('', 422); }
}
foreach ([1 => 'Gestor Um', 2 => 'Gestor Dois', 3 => 'Membro', 4 => 'Disponível'] as $id => $nome) {
    App\Models\Colaborador::create(['id' => $id, 'nome' => $nome, 'email' => "usuario$id@example.test", 'perfil' => 'Teste', 'perfil_id' => 1, 'ativo' => true]);
}
$r1 = App\Models\Responsavel::create(['colaborador_id' => 1, 'cargo' => 'Gestor']);
$r2 = App\Models\Responsavel::create(['colaborador_id' => 2, 'cargo' => 'Gestor']);
App\Models\Colaborador::find(3)->update(['responsavel_id' => $r1->id]);
$base = "/responsaveis/{$r1->id}/equipe";
$all = ['responsaveis.listar', 'responsaveis.visualizar', 'responsaveis.editar', 'responsaveis.excluir', 'responsaveis.equipe.listar', 'responsaveis.equipe.criar', 'responsaveis.equipe.excluir'];
$own = [...$all, 'responsaveis.minha_equipe'];
check(callRoute('GET', $base, [])->getStatusCode() === 403, 'Listagem exige permissão');
$html = callRoute('GET', $base, $all);
check($html->getStatusCode() === 200 && str_contains($html->getContent(), 'Equipe de colaboradores do responsável Gestor Um'), 'Tela renderizada com responsável');
$json = json_decode(callRoute('GET', '/responsaveis', $own, 1, [], true)->getContent(), true);
check($json['recordsTotal'] === 1 && $json['data'][0]['id'] === $r1->id, 'Minha equipe restringe listagem');
check(str_contains($json['data'][0]['acoes'], 'Colaboradores da Equipe'), 'Botão da equipe disponível com permissão');
$json = json_decode(callRoute('GET', '/responsaveis', ['responsaveis.listar'], 1, [], true)->getContent(), true);
check($json['recordsTotal'] === 2 && !str_contains($json['data'][0]['acoes'], 'Colaboradores da Equipe'), 'Sem restrição lista todos; sem permissão oculta botão');
foreach (["/responsaveis/{$r2->id}", "/responsaveis/{$r2->id}/editar", "/responsaveis/{$r2->id}/equipe", "/responsaveis/{$r2->id}/equipe/pesquisar"] as $uri) {
    check(callRoute('GET', $uri, $own)->getStatusCode() === 403, "Bloqueia acesso direto: $uri");
}
check(callRoute('POST', "/responsaveis/{$r2->id}/equipe", $own, 1, ['colaborador_id' => 4])->getStatusCode() === 403, 'Bloqueia adição em outra equipe');
check(callRoute('POST', $base, ['responsaveis.equipe.listar'], 1, ['colaborador_id' => 4])->getStatusCode() === 403, 'Adição exige permissão criar');
check(callRoute('POST', $base, $own, 1, ['colaborador_id' => 4])->getStatusCode() === 302 && App\Models\Colaborador::find(4)->responsavel_id === $r1->id, 'Adiciona colaborador');
check(callRoute('POST', "/responsaveis/{$r2->id}/equipe", $all, 1, ['colaborador_id' => 4])->getStatusCode() === 422, 'Não transfere membro de outra equipe silenciosamente');
$json = json_decode(callRoute('GET', $base, $own, 1, ['order' => [['column' => 2, 'dir' => 'desc']], 'search' => ['value' => '0']], true)->getContent(), true);
check($json['recordsTotal'] === 2 && $json['data'][0]['justificativas_count'] === 0, 'DataTable conta, pesquisa e ordena justificativas');
check(callRoute('DELETE', "$base/4", ['responsaveis.equipe.listar'])->getStatusCode() === 403, 'Remoção exige permissão excluir');
check(callRoute('DELETE', "/responsaveis/{$r2->id}/equipe/4", $own)->getStatusCode() === 403, 'Bloqueia remoção em outra equipe');
check(callRoute('DELETE', "/responsaveis/{$r2->id}/equipe/4", $all)->getStatusCode() === 404, 'Recusa membro que não pertence à equipe da URL');
check(callRoute('DELETE', "$base/4", $own)->getStatusCode() === 302 && App\Models\Colaborador::find(4)->responsavel_id === null, 'Remove vínculo preservando colaborador');
$json = json_decode(callRoute('GET', '/responsaveis', $own, 4, [], true)->getContent(), true);
check($json['recordsTotal'] === 0, 'Usuário sem cadastro de responsável não vê terceiros');

$categoria = App\Models\Categoria::create(['nome' => 'Teste', 'ativo' => true]);
foreach ([3, 3, 4] as $colaboradorId) {
    App\Models\Justificativa::create(['colaborador_id' => $colaboradorId, 'categoria_id' => $categoria->id, 'status' => 'pendente']);
}
App\Models\Justificativa::where('colaborador_id', 3)->first()->delete();
$json = json_decode(callRoute('GET', $base, $own, 1, ['search' => ['value' => 'Membro']], true)->getContent(), true);
check($json['recordsFiltered'] === 1 && $json['data'][0]['justificativas_count'] === 1, 'Contagem pertence ao membro e desconsidera justificativas excluídas');
