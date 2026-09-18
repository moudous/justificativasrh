<?php

// Execute com: php tests/justificativas-colaborador.php
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

foreach ([1 => 'Colaborador Um', 2 => 'Colaborador Dois', 3 => 'Responsável'] as $id => $nome) {
    App\Models\Colaborador::create(['id' => $id, 'nome' => $nome, 'email' => "usuario$id@example.test", 'perfil' => 'RH', 'perfil_id' => 1, 'ativo' => true]);
}
$responsavel = App\Models\Responsavel::create(['colaborador_id' => 3, 'cargo' => 'Gestor']);
App\Models\Colaborador::find(1)->update(['responsavel_id' => $responsavel->id]);
$categoria = App\Models\Categoria::create(['nome' => 'Ocorrência', 'ativo' => true]);
$all = array_map(fn ($p) => 'justificativas_colaborador.'.$p, ['listar', 'visualizar', 'criar', 'editar', 'excluir', 'historico', 'enviar_responsavel']);
$base = '/justificativas_colaborador';
$dados = ['descricao' => 'Consulta', 'categoria_id' => $categoria->id, 'tipo_ocorrencia' => 'data', 'data_ocorrencia' => '2026-09-18', 'hora_inicial' => '08:00', 'hora_final' => '09:00', 'atestado_medico' => 0, 'colaborador_id' => 2];
check(callRoute('GET', $base, [])->getStatusCode() === 403, 'Listagem exige nova permissão');
check(callRoute('GET', $base, ['justificativa.listar'])->getStatusCode() === 403, 'Permissão antiga não libera nova rota');
check(callRoute('GET', "$base/criar", $all)->getStatusCode() === 200, 'Formulário de criação renderiza');
check(callRoute('POST', $base, $all, 1, $dados)->getStatusCode() === 302, 'Cria justificativa pela nova área');
$j = App\Models\Justificativa::latest('id')->firstOrFail();
check($j->colaborador_id === 1 && $j->controle === 'colaborador', 'Cadastro vincula ao usuário logado e ignora titular informado');
$outro = App\Models\Justificativa::create(['colaborador_id' => 2, 'categoria_id' => $categoria->id, 'status' => 'Rascunho', 'controle' => 'colaborador']);
$uri = "$base/{$j->id}";
foreach (["$base/{$outro->id}", "$base/{$outro->id}/historico", "$base/{$outro->id}/editar", "$base/{$outro->id}/anexos/1"] as $path) {
    check(callRoute('GET', $path, $all)->getStatusCode() === 404, "Isola titular mesmo com perfil RH: $path");
}
foreach (['PUT' => "$base/{$outro->id}", 'DELETE' => "$base/{$outro->id}", 'PATCH' => "$base/{$outro->id}/enviar-responsavel"] as $method => $path) {
    check(callRoute($method, $path, $all, 1, $dados)->getStatusCode() === 404, 'Bloqueia mutação de terceiro: '.$method);
}
$render = callRoute('GET', $uri, $all);
check($render->getStatusCode() === 200 && str_contains($render->getContent(), "$uri/editar"), 'Visualização usa links da nova área');
check(callRoute('GET', "$uri/editar", $all)->getStatusCode() === 200, 'Edita antes de enviar');
check(callRoute('GET', "$uri/historico", $all)->getStatusCode() === 200, 'Consulta histórico próprio');
check(callRoute('PUT', $uri, $all, 1, [...$dados, 'descricao' => 'Atualizada'])->getStatusCode() === 302 && $j->fresh()->descricao === 'Atualizada', 'Salva edição própria');
$json = json_decode(callRoute('GET', $base, $all, 1, [], true)->getContent(), true);
check($json['recordsTotal'] === 1 && str_contains($json['data'][0]['acoes'], 'Enviar p/ Responsavel'), 'Lista somente próprias e oferece envio');
$json = json_decode(callRoute('GET', $base, ['justificativas_colaborador.listar'], 1, [], true)->getContent(), true);
check(!str_contains($json['data'][0]['acoes'], '<a ') && !str_contains($json['data'][0]['acoes'], '<button'), 'Ações ocultas sem permissões');
foreach (['GET' => "$uri/editar", 'PUT' => $uri, 'DELETE' => $uri, 'PATCH' => "$uri/enviar-responsavel"] as $method => $path) {
    check(callRoute($method, $path, ['justificativas_colaborador.listar'], 1, $dados)->getStatusCode() === 403, 'Permissão exigida: '.$method.' '.$path);
}
check(callRoute('PATCH', "$uri/enviar-responsavel", $all)->getStatusCode() === 200, 'Envia para responsável');
check($j->fresh()->controle === 'gestao' && $j->fresh()->status === 'Enviada ao responsável', 'Envio passa para gestão e atualiza situação');
check($j->historicos()->where('evento', 'controle_alterado')->count() === 1, 'Envio registrado no histórico');
foreach (['GET' => "$uri/editar", 'PUT' => $uri, 'DELETE' => $uri, 'PATCH' => "$uri/enviar-responsavel"] as $method => $path) {
    check(callRoute($method, $path, $all, 1, $dados)->getStatusCode() === 403, 'Após envio bloqueia: '.$method.' '.$path);
}
check(callRoute('DELETE', "$uri/anexos/1", $all)->getStatusCode() === 403, 'Após envio bloqueia exclusão de anexo');
$json = json_decode(callRoute('GET', $base, $all, 1, [], true)->getContent(), true);
$acoes = $json['data'][0]['acoes'];
check($json['recordsTotal'] === 1 && str_contains($json['data'][0]['situacao'], 'Com o responsável') && !str_contains($acoes, '<button') && !str_contains($acoes, '/editar'), 'Enviada continua visível somente para consulta e histórico');
check(callRoute('GET', $uri, $all)->getStatusCode() === 200 && callRoute('GET', "$uri/historico", $all)->getStatusCode() === 200, 'Consulta e histórico disponíveis após envio');
check(callRoute('PATCH', "$base/{$outro->id}/enviar-responsavel", $all, 2)->getStatusCode() === 422 && $outro->fresh()->controle === 'colaborador', 'Sem responsável não envia nem altera etapa');
check(callRoute('DELETE', "$base/{$outro->id}", $all, 2)->getStatusCode() === 302 && $outro->fresh()->trashed(), 'Exclui própria não enviada');
$j->refresh()->update(['controle' => 'colaborador']);
check(callRoute('GET', "$uri/editar", $all)->getStatusCode() === 200, 'Devolução à etapa do colaborador permite corrigir');

$antigas = ['justificativa.listar', 'justificativa.visualizar', 'justificativa.editar', 'justificativa.historico'];
foreach (['/justificativas', "/justificativas/{$j->id}", "/justificativas/{$j->id}/editar", "/justificativas/{$j->id}/historico"] as $path) {
    check(callRoute('GET', $path, $antigas)->getStatusCode() === 200, 'Tela existente preservada: '.$path);
}

$etapas = [];
foreach (['gestao', 'rh', 'aprovado'] as $controle) {
    $etapas[$controle] = App\Models\Justificativa::create([
        'colaborador_id' => 1,
        'categoria_id' => $categoria->id,
        'status' => ucfirst($controle),
        'controle' => $controle,
    ]);
}
$rejeitadaGestor = App\Models\Justificativa::create([
    'colaborador_id' => 1,
    'categoria_id' => $categoria->id,
    'status' => 'Rejeitada',
    'controle' => 'reprovado',
]);
$rejeitadaGestor->historicos()->create([
    'evento' => 'controle_alterado',
    'etapa_controle' => 'gestao',
    'historico' => 'Rejeitado pelo Gestor',
]);
$rejeitadaRh = App\Models\Justificativa::create([
    'colaborador_id' => 1,
    'categoria_id' => $categoria->id,
    'status' => 'Rejeitada',
    'controle' => 'reprovado',
]);
$rejeitadaRh->historicos()->create([
    'evento' => 'controle_alterado',
    'etapa_controle' => 'rh',
    'historico' => 'Rejeitado pelo RH',
]);
$json = json_decode(callRoute('GET', $base, $all, 1, [], true)->getContent(), true);
$situacoes = collect($json['data'])->pluck('situacao')->implode(' ');
check($json['recordsTotal'] === 6, 'Lista todas as justificativas próprias em qualquer etapa');
check(str_contains($situacoes, 'Rejeitada pelo responsável'), 'Identifica rejeição feita pelo responsável');
check(str_contains($situacoes, 'Rejeitada pelo RH'), 'Identifica rejeição feita pelo RH');
check(! collect($json['data'])->contains(fn ($registro) => $registro['id'] === $outro->id), 'Não mistura justificativas de outro colaborador');

App\Models\JustificativaHistorico::creating(function ($historico) {
    if ($historico->evento === 'controle_alterado') throw new RuntimeException('Falha simulada no histórico');
});
check(callRoute('PATCH', "$uri/enviar-responsavel", $all)->getStatusCode() === 500 && $j->fresh()->controle === 'colaborador', 'Falha no histórico reverte envio por completo');
