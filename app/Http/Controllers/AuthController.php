<?php

namespace App\Http\Controllers;

use App\Models\AppSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthController extends Controller
{
    /**
     * Tampilkan halaman autentikasi produksi (Tanpa membocorkan daftar akun/backdoor).
     */
    public function showLogin(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.login', [
            'branding' => AppSetting::allSettings(),
            'backgroundUrl' => AppSetting::fileUrl('login_background'),
            'logoUrl' => AppSetting::fileUrl('login_logo'),
        ]);
    }

    /**
     * Autentikasi standar produksi dengan Rate Limiting (Anti Brute-Force),
     * Pengecekan Status Aktif, & Regenerasi Session ID (Anti Session Fixation).
     */
    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'string', 'email', 'max:255'],
            'password' => ['required', 'string', 'max:255'],
        ]);

        $throttleKey = Str::transliterate(Str::lower($credentials['email']) . '|' . $request->ip());

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            throw ValidationException::withMessages([
                'email' => "Terlalu banyak percobaan masuk. Demi keamanan sistem, silakan tunggu {$seconds} detik lagi.",
            ]);
        }

        $attemptCredentials = [
            'email' => $credentials['email'],
            'password' => $credentials['password'],
            'is_active' => true,
        ];

        if (! Auth::attempt($attemptCredentials, $request->boolean('remember'))) {
            RateLimiter::hit($throttleKey, 60);

            throw ValidationException::withMessages([
                'email' => 'Kredensial email atau kata sandi tidak sesuai, atau akun sedang dinonaktifkan.',
            ]);
        }

        RateLimiter::clear($throttleKey);
        $request->session()->regenerate();

        $user = Auth::user();

        return redirect()->intended(route('dashboard'))
            ->with('success', 'Autentikasi berhasil. Selamat bertugas, ' . $user->name . ' (' . $user->role->label() . ').');
    }

    /**
     * Pembaruan kata sandi pengguna terautentikasi dengan validasi hash saat ini.
     */
    public function updatePassword(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        $request->user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        return back()->with('success', 'Kata sandi keamanan akun Anda berhasil diperbarui.');
    }

    /**
     * Akhiri sesi pengguna dengan invalidasi session & regenerasi CSRF token.
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->with('success', 'Sesi Anda telah diakhiri dengan aman.');
    }
}
