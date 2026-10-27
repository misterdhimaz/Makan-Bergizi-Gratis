<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user || ! $user->is_active) {
            return redirect()->route('login')->with('error', 'Silakan masuk terlebih dahulu.');
        }

        if (! $user->hasRole($roles)) {
            return redirect()->route('dashboard')->with(
                'error',
                'Akses Dibatasi: Hak akses Anda (' . $user->role->label() . ') tidak memiliki izin membuka modul tersebut.'
            );
        }

        return $next($request);
    }
}

