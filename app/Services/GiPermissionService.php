<?php

namespace App\Services;

use Illuminate\Http\Request;

class GiPermissionService
{
    public function todas(?Request $request = null): array
    {
        $request ??= request();

        return collect((array) $request->session()->get('gi_context.permissoes', []))
            ->filter(fn ($permissao): bool => is_string($permissao) && trim($permissao) !== '')
            ->map(fn (string $permissao): string => trim($permissao))
            ->unique()
            ->values()
            ->all();
    }

    public function permite(string $permissao, ?Request $request = null): bool
    {
        return in_array($permissao, $this->todas($request), true);
    }

    public function exigir(string $permissao, ?Request $request = null): void
    {
        abort_unless(
            $this->permite($permissao, $request),
            403,
            "Seu perfil não possui a permissão {$permissao}.",
        );
    }
}
