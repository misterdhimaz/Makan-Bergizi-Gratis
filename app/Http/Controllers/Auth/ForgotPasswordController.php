<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\PasswordResetRequest;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ForgotPasswordController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'identifier' => ['required', 'string', 'max:255'],
            'reason' => ['required', 'string', 'max:255'],
        ]);

        $identifier = trim($validated['identifier']);
        $user = User::where('username', $identifier)
            ->orWhere('email', $identifier)
            ->first();

        PasswordResetRequest::create([
            'user_id' => $user?->id,
            'identifier' => $identifier,
            'reason' => $validated['reason'],
            'status' => 'pending',
        ]);

        AuditLogService::record(
            action: 'lupa_sandi',
            module: 'Autentikasi',
            target: $identifier,
            oldValues: null,
            newValues: ['identifier' => $identifier, 'reason' => $validated['reason']],
            reason: $validated['reason'],
            actor: $user,
        );

        return redirect()->route('login.show')
            ->with('success', 'Permintaan reset kata sandi telah dikirim ke Administrator Sistem dan dicatat di Log Aktivitas.');
    }
}
