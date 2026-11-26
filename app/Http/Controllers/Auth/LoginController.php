<?php

namespace App\Http\Controllers\Auth;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\SystemSettingService;
use Illuminate\Auth\Events\Failed;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function __construct(
        private readonly SystemSettingService $settings,
    ) {}

    public function show(): View|RedirectResponse
    {
        if (Auth::check()) {
            return $this->redirectAfterLogin(Auth::user());
        }

        return view('auth.login', [
            'sessionTimeout' => $this->settings->getInt('session_idle_timeout_minutes', 30),
            'maxFailedAttempts' => $this->settings->getInt('max_failed_login_attempts', 5),
            'lockDuration' => $this->settings->getInt('account_lock_duration_minutes', 15),
        ]);
    }

    public function login(Request $request): RedirectResponse
    {
        $identifierInput = $request->input('username') ?: $request->input('email');
        $request->merge(['identifier' => $identifierInput]);

        $validated = $request->validate([
            'identifier' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string', 'max:255'],
        ]);

        $identifier = trim($validated['identifier']);
        $maxAttempts = max(1, $this->settings->getInt('max_failed_login_attempts', 5));
        $lockMinutes = max(1, $this->settings->getInt('account_lock_duration_minutes', 15));

        $user = User::where('username', $identifier)
            ->orWhere('email', $identifier)
            ->first();

        if (! $user) {
            event(new Failed('web', null, ['username' => $identifier]));

            return back()
                ->withInput(['username' => $identifier, 'email' => $identifier])
                ->with('auth_status_type', 'invalid')
                ->withErrors(['username' => 'Username atau kata sandi salah.']);
        }

        if (! $user->is_active) {
            event(new Failed('web', $user, ['username' => $identifier]));

            return back()
                ->withInput(['username' => $identifier, 'email' => $identifier])
                ->with('auth_status_type', 'inactive')
                ->withErrors(['username' => 'Akun Anda dinonaktifkan. Hubungi Administrator Sistem.']);
        }

        if ($user->locked_until && $user->locked_until->isFuture()) {
            $remaining = max(1, (int) ceil(now()->diffInSeconds($user->locked_until) / 60));

            return back()
                ->withInput(['username' => $identifier, 'email' => $identifier])
                ->with('auth_status_type', 'locked')
                ->withErrors(['username' => "Akun terkunci sementara selama {$remaining} menit karena {$maxAttempts} kali gagal masuk."]);
        }

        if (! Hash::check($validated['password'], $user->password)) {
            $attempts = (int) $user->failed_login_attempts + 1;
            $updates = ['failed_login_attempts' => $attempts];

            if ($attempts >= $maxAttempts) {
                $updates['locked_until'] = now()->addMinutes($lockMinutes);
                $user->forceFill($updates)->saveQuietly();
                event(new Failed('web', $user, ['username' => $identifier]));

                return back()
                    ->withInput(['username' => $identifier, 'email' => $identifier])
                    ->with('auth_status_type', 'locked')
                    ->withErrors(['username' => "Akun terkunci sementara selama {$lockMinutes} menit karena {$maxAttempts} kali gagal masuk."]);
            }

            $user->forceFill($updates)->saveQuietly();
            event(new Failed('web', $user, ['username' => $identifier]));

            return back()
                ->withInput(['username' => $identifier, 'email' => $identifier])
                ->with('auth_status_type', 'invalid')
                ->withErrors(['username' => 'Username atau kata sandi salah.']);
        }

        if ($user->must_change_password && $user->password_expires_at && $user->password_expires_at->isPast()) {
            throw ValidationException::withMessages([
                'username' => 'Kata sandi sementara sudah kedaluwarsa (24 jam). Silakan ajukan reset kata sandi ke Administrator Sistem.',
            ]);
        }

        $user->forceFill([
            'failed_login_attempts' => 0,
            'locked_until' => null,
            'last_login_at' => now(),
        ])->saveQuietly();

        Auth::login($user, false);
        $request->session()->regenerate();
        $request->session()->put('last_activity_timestamp', now()->timestamp);

        if ($user->must_change_password) {
            return redirect()->route('password.change')
                ->with('warning', 'Anda wajib mengganti kata sandi sementara pada login pertama sebelum masuk ke sistem.');
        }

        return $this->redirectAfterLogin($user)
            ->with('success', 'Autentikasi berhasil. Selamat bertugas, '.$user->name.' ('.$user->role->label().').');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login.show')
            ->with('success', 'Sesi Anda telah diakhiri dengan aman.');
    }

    private function redirectAfterLogin(User $user): RedirectResponse
    {
        if ($user->role === UserRole::ADMIN && Route::has('admin.dashboard')) {
            return redirect()->route('admin.dashboard');
        }

        return redirect()->route('dashboard');
    }
}
