<?php

namespace App\Http\Middleware;

use App\Models\Justificativa;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class OwnColaboradorJustificativa
{
    public function handle(Request $request, Closure $next): Response
    {
        $id = filter_var($request->session()->get('gi_context.usuario.id'), FILTER_VALIDATE_INT);
        abort_unless($id !== false && $id > 0, 401);
        $parametro = $request->route('justificativa');
        if ($parametro === null) {
            return $next($request);
        }

        $executar = function () use ($request, $next, $id, $parametro): Response {
            $query = Justificativa::query()->where('colaborador_id', $id);
            if ($request->routeIs('justificativas_colaborador.historico')) $query->withTrashed();
            if (! $request->isMethodSafe()) $query->lockForUpdate();
            $registro = $query->findOrFail($parametro instanceof Justificativa ? $parametro->id : $parametro);
            if (! $request->isMethodSafe() || $request->routeIs('justificativas_colaborador.edit')) {
                abort_unless($registro->controle === 'colaborador', 403, 'A justificativa já foi enviada e não pode ser alterada pelo colaborador.');
            }
            // Usa o estado relido sob bloqueio para evitar edição simultânea ao envio.
            if ($parametro instanceof Justificativa) $parametro->setRawAttributes($registro->getAttributes(), true);
            return $next($request);
        };

        if ($request->isMethodSafe()) return $executar();

        DB::beginTransaction();
        try {
            $response = $executar();
            // O pipeline pode converter exceções em respostas antes de retorná-las.
            if ($response->getStatusCode() >= 400) {
                DB::rollBack();
            } else {
                DB::commit();
            }
            return $response;
        } catch (\Throwable $erro) {
            DB::rollBack();
            throw $erro;
        }
    }
}
