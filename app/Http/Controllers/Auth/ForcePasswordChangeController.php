<?php

namespace App\Http\Controllers\Auth;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Services\AuditLogService;
use App\Services\SystemSettingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class ForcePasswordChangeController extends Controller
{
    public function __construct(
        private readonly SystemSettingService $settings,
    ) {}

    public function show(): View
    {
        return view('auth.force-password-change', [
            'minLength' => max(8, $this->settings->getInt('password_min_length', 8)),
            'requireMixedCaseNumber' => $this->settings->getBool('password_require_mixed_case_number', true),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $minLength = max(8, $this->settings->getInt('password_min_length', 8));
        $requireMixed = $this->settings->getBool('password_require_mixed_case_number', true);

        $passwordRule = Password::min($minLength);
        if ($requireMixed) {
            $passwordRule = $passwordRule->mixedCase()->numbers();
        }

        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', $passwordRule],
        ]);

        $user = $request->user();

        $user->forceFill([
            'password' => Hash::make($validated['password']),
            'must_change_password' => false,
            'password_expires_at' => null,
        ])->saveQuietly();

        AuditLogService::record(
            action: 'ganti_sandi',
            module: 'Autentikasi',
            target: $user->username ?? $user->email,
            oldValues: null,
            newValues: ['must_change_password' => false],
            reason: 'Pengguna mengganti kata sandi sementara',
            actor: $user,
        );

        $targetRoute = $user->role === UserRole::ADMIN && Route::has('admin.dashboard')
            ? route('admin.dashboard')
            : route('dashboard');

        return redirect()->to($targetRoute)
            ->with('success', 'Kata sandi baru berhasil disimpan. Selamat bertugas.');
    }
}
