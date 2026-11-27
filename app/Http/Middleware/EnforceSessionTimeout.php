<?php

namespace App\Http\Middleware;

use App\Services\SystemSettingService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnforceSessionTimeout
{
    public function __construct(
        private readonly SystemSettingService $settings,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $timeoutMinutes = max(1, $this->settings->getInt('session_idle_timeout_minutes', 30));
            $lastActivity = (int) $request->session()->get('last_activity_timestamp', 0);
            $nowTimestamp = now()->timestamp;

            if ($lastActivity > 0 && ($nowTimestamp - $lastActivity) > ($timeoutMinutes * 60)) {
                Auth::guard('web')->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('login.show')
                    ->with('auth_status_type', 'session_expired')
                    ->with('error', 'Sesi berakhir karena tidak aktif. Silakan masuk kembali.');
            }

            $request->session()->put('last_activity_timestamp', $nowTimestamp);
        }

        return $next($request);
    }
}
