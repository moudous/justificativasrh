<?php

namespace App\Http\Middleware;

use App\Services\GiPermissionService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

class EnsureGiSession
{
    public function handle(Request $request, Closure $next): Response
    {
        View::share('giPermissoes', app(GiPermissionService::class));

        abort_unless(
            filter_var(env('GI_ALLOW_OUTSIDE_IFRAME', false), FILTER_VALIDATE_BOOL)
                || $request->session()->has('gi_context'),
            401,
            'Abra esta aplicação pelo menu do GI.',
        );

        return $next($request);
    }
}
