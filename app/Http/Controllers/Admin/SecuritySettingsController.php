<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use App\Services\AuditLogService;
use App\Services\SystemSettingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SecuritySettingsController extends Controller
{
    public function __construct(
        private readonly SystemSettingService $settings,
    ) {}

    /**
     * Menampilkan halaman pengaturan keamanan sesi dan kebijakan kata sandi.
     */
    public function edit(): View
    {
        return view('admin.settings.keamanan-sesi', [
            'values' => [
                'session_idle_timeout_minutes' => $this->settings->getInt('session_idle_timeout_minutes', 30),
                'max_failed_login_attempts' => $this->settings->getInt('max_failed_login_attempts', 5),
                'account_lock_duration_minutes' => $this->settings->getInt('account_lock_duration_minutes', 15),
                'password_min_length' => $this->settings->getInt('password_min_length', 8),
                'password_require_mixed_case_number' => $this->settings->getBool('password_require_mixed_case_number', true),
                'force_password_change_first_login' => $this->settings->getBool('force_password_change_first_login', true),
                'logout_on_role_change' => $this->settings->getBool('logout_on_role_change', true),
            ],
        ]);
    }

    /**
     * Memperbarui pengaturan keamanan sesi & kebijakan sandi serta mencatat ke Audit Log.
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'session_idle_timeout_minutes' => ['required', 'integer', 'min:5', 'max:1440'],
            'max_failed_login_attempts' => ['required', 'integer', 'min:3', 'max:20'],
            'account_lock_duration_minutes' => ['required', 'integer', 'min:1', 'max:1440'],
            'password_min_length' => ['required', 'integer', 'min:6', 'max:64'],
            'password_require_mixed_case_number' => ['nullable', 'boolean'],
            'force_password_change_first_login' => ['nullable', 'boolean'],
            'logout_on_role_change' => ['nullable', 'boolean'],
        ]);

        $newValues = [
            'session_idle_timeout_minutes' => (string) $validated['session_idle_timeout_minutes'],
            'max_failed_login_attempts' => (string) $validated['max_failed_login_attempts'],
            'account_lock_duration_minutes' => (string) $validated['account_lock_duration_minutes'],
            'password_min_length' => (string) $validated['password_min_length'],
            'password_require_mixed_case_number' => $request->boolean('password_require_mixed_case_number') ? '1' : '0',
            'force_password_change_first_login' => $request->boolean('force_password_change_first_login') ? '1' : '0',
            'logout_on_role_change' => $request->boolean('logout_on_role_change') ? '1' : '0',
        ];

        $oldValues = [];
        foreach ($newValues as $key => $val) {
            $oldValues[$key] = (string) $this->settings->get($key, '');
            SystemSetting::updateOrCreate(
                ['key' => $key],
                [
                    'value' => $val,
                    'group' => str_starts_with($key, 'password_') || $key === 'force_password_change_first_login'
                        ? 'password'
                        : 'security',
                    'updated_by' => $request->user()?->id,
                ],
            );
        }

        AuditLogService::record(
            action: 'ubah_pengaturan',
            module: 'Pengaturan Sistem · Keamanan Sesi',
            target: 'Kebijakan Keamanan & Sesi',
            oldValues: $oldValues,
            newValues: $newValues,
            reason: 'Pembaruan kebijakan keamanan sesi oleh Administrator Sistem',
            actor: $request->user(),
        );

        return redirect()
            ->route('admin.settings.keamanan-sesi')
            ->with('success', 'Pengaturan keamanan sesi dan kebijakan kata sandi berhasil disimpan.');
    }
}
