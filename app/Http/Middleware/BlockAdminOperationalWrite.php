<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class BlockAdminOperationalWrite
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (
            $user
            && $user->role === UserRole::ADMIN
            && in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'], true)
        ) {
            abort(403, 'Administrator Sistem tidak diizinkan membuat, mengubah, atau menghapus transaksi operasional harian.');
        }

        return $next($request);
    }
}
