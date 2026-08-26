<?php

namespace App\Services;

use App\Models\Colaborador;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use UnexpectedValueException;

class GiColaboradorSynchronizer
{
    public function syncFromGi(string $accessToken): int
    {
        $response = Http::withToken($accessToken)->acceptJson()->timeout(30)
            ->get(rtrim(config('gi.gi_url'), '/').'/api/integracoes/v1/usuarios');

        abort_unless($response->successful(), 502, 'Não foi possível importar os colaboradores do GI.');

        return $this->syncMany((array) $response->json('data', []));
    }

    public function syncMany(array $usuarios): int
    {
        $total = 0;

        foreach ($usuarios as $usuario) {
            if (! is_array($usuario)) {
                continue;
            }

            $this->sync([
                'usuario' => $usuario,
                'perfil' => $usuario['perfil'] ?? ($usuario['perfis'][0] ?? []),
                'sistema' => $usuario['sistema'] ?? [],
            ]);
            $total++;
        }

        return $total;
    }

    public function sync(array $contexto): Colaborador
    {
        $usuario = (array) ($contexto['usuario'] ?? []);
        $perfil = (array) ($contexto['perfil'] ?? []);
        $sistema = (array) ($contexto['sistema'] ?? []);

        $id = filter_var($usuario['id'] ?? null, FILTER_VALIDATE_INT);
        $perfilId = filter_var($perfil['id'] ?? null, FILTER_VALIDATE_INT);
        $sistemaId = filter_var($sistema['id'] ?? null, FILTER_VALIDATE_INT);

        if ($id === false || $id < 1 || $perfilId === false || $perfilId < 1 || $sistemaId === false || $sistemaId < 1) {
            throw new UnexpectedValueException('O GI não informou um usuário com perfil válido neste sistema.');
        }

        $dados = [
            'nome' => trim((string) ($usuario['nome'] ?? '')),
            'email' => trim((string) ($usuario['email'] ?? '')),
            'perfil' => trim((string) ($perfil['nome'] ?? '')),
            'perfil_id' => $perfilId,
            'ativo' => array_key_exists('ativo', $usuario) ? (bool) $usuario['ativo'] : true,
        ];

        if ($dados['nome'] === '' || $dados['email'] === '' || $dados['perfil'] === '') {
            throw new UnexpectedValueException('O GI não informou nome, e-mail e perfil do usuário.');
        }

        return DB::transaction(function () use ($id, $dados): Colaborador {
            $colaborador = Colaborador::query()->find($id);

            if (! $colaborador) {
                $colaborador = Colaborador::query()->where('email', $dados['email'])->first();
                if ($colaborador) {
                    $colaborador->id = $id;
                }
            }

            $colaborador ??= new Colaborador(['id' => $id]);
            $colaborador->fill($dados)->save();

            return $colaborador;
        });
    }
}
