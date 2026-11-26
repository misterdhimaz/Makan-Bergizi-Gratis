<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePasswordChanged
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (
            $user
            && $user->must_change_password
            && ! $request->routeIs('password.change', 'password.change.store', 'logout')
        ) {
            return redirect()->route('password.change')
                ->with('warning', 'Anda wajib mengganti kata sandi sementara pada login pertama sebelum masuk ke sistem.');
        }

        return $next($request);
    }
}
