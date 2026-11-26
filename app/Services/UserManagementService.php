<?php

namespace App\Services;

use App\Enums\UserRole;
use App\Models\PasswordResetRequest;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class UserManagementService
{
    /**
     * Membuat akun baru beserta kata sandi sementara acak (wajib diganti dalam 24 jam).
     *
     * @param  array{name: string, username: string, email: string, role: string, location_id?: int|null}  $data
     * @return array{user: User, temporary_password: string}
     */
    public function create(array $data): array
    {
        $this->assertGuruPicLocationRules(
            role: $data['role'],
            locationId: $data['location_id'] ?? null,
        );

        $temporaryPassword = $this->generateTemporaryPassword();

        $user = User::create([
            'name' => $data['name'],
            'username' => $data['username'],
            'email' => $data['email'],
            'role' => $data['role'],
            'location_id' => $this->resolveLocationId($data['role'], $data['location_id'] ?? null),
            'password' => Hash::make($temporaryPassword),
            'is_active' => true,
            'must_change_password' => true,
            'password_expires_at' => now()->addHours(24),
            'failed_login_attempts' => 0,
            'locked_until' => null,
        ]);

        return [
            'user' => $user,
            'temporary_password' => $temporaryPassword,
        ];
    }

    /**
     * Memperbarui profil akun tanpa mengubah kata sandi.
     *
     * @param  array{name: string, username: string, email: string, role: string, location_id?: int|null}  $data
     */
    public function update(User $user, array $data): User
    {
        $this->assertGuruPicLocationRules(
            role: $data['role'],
            locationId: $data['location_id'] ?? null,
            ignoreUserId: $user->id,
        );

        $user->update([
            'name' => $data['name'],
            'username' => $data['username'],
            'email' => $data['email'],
            'role' => $data['role'],
            'location_id' => $this->resolveLocationId($data['role'], $data['location_id'] ?? null),
        ]);

        return $user->refresh();
    }

    /**
     * Menonaktifkan akun (bukan menghapus).
     */
    public function deactivate(User $user, User $actor, ?string $reason = null): User
    {
        if ($user->id === $actor->id) {
            throw ValidationException::withMessages([
                'user' => 'Anda tidak dapat menonaktifkan akun yang sedang digunakan.',
            ]);
        }

        if (! $user->is_active) {
            return $user;
        }

        // Catat aksi spesifik nonaktifkan (bukan generic "ubah").
        AuditLogService::$enabled = false;
        $user->update(['is_active' => false]);
        AuditLogService::$enabled = true;

        AuditLogService::record(
            action: 'nonaktifkan',
            module: 'Pengguna & Peran',
            target: $user->username,
            oldValues: ['is_active' => true],
            newValues: ['is_active' => false],
            reason: $reason ?? 'Dinonaktifkan oleh Administrator Sistem',
            actor: $actor,
        );

        return $user->refresh();
    }

    /**
     * Mengaktifkan kembali akun.
     */
    public function activate(User $user, User $actor): User
    {
        if ($user->is_active) {
            return $user;
        }

        if ($user->role === UserRole::GURU_PIC) {
            $this->assertGuruPicLocationRules(
                role: UserRole::GURU_PIC->value,
                locationId: $user->location_id,
                ignoreUserId: $user->id,
            );
        }

        AuditLogService::$enabled = false;
        $user->update([
            'is_active' => true,
            'failed_login_attempts' => 0,
            'locked_until' => null,
        ]);
        AuditLogService::$enabled = true;

        AuditLogService::record(
            action: 'aktifkan',
            module: 'Pengguna & Peran',
            target: $user->username,
            oldValues: ['is_active' => false],
            newValues: ['is_active' => true],
            actor: $actor,
        );

        return $user->refresh();
    }

    /**
     * Mereset kata sandi menjadi sementara acak (berlaku 24 jam, wajib diganti).
     *
     * @return array{user: User, temporary_password: string}
     */
    public function resetPassword(User $user, User $actor, ?string $reason = null): array
    {
        $temporaryPassword = $this->generateTemporaryPassword();

        DB::transaction(function () use ($user, $actor, $temporaryPassword, $reason): void {
            // Hindari double-log dari trait Auditable; catat aksi spesifik reset_sandi.
            AuditLogService::$enabled = false;

            $user->update([
                'password' => Hash::make($temporaryPassword),
                'must_change_password' => true,
                'password_expires_at' => now()->addHours(24),
                'failed_login_attempts' => 0,
                'locked_until' => null,
            ]);

            AuditLogService::$enabled = true;

            AuditLogService::record(
                action: 'reset_sandi',
                module: 'Pengguna & Peran',
                target: $user->username,
                oldValues: null,
                newValues: [
                    'must_change_password' => true,
                    'password_expires_at' => $user->password_expires_at?->toIso8601String(),
                ],
                reason: $reason ?? 'Reset kata sandi oleh Administrator Sistem',
                actor: $actor,
            );

            PasswordResetRequest::query()
                ->where(function ($query) use ($user): void {
                    $query->where('user_id', $user->id)
                        ->orWhere('identifier', $user->username)
                        ->orWhere('identifier', $user->email);
                })
                ->where('status', 'pending')
                ->update([
                    'status' => 'handled',
                    'handled_by' => $actor->id,
                    'handled_at' => now(),
                ]);
        });

        return [
            'user' => $user->refresh(),
            'temporary_password' => $temporaryPassword,
        ];
    }

    public function generateTemporaryPassword(): string
    {
        return Str::password(12, letters: true, numbers: true, symbols: false, spaces: false);
    }

    private function resolveLocationId(string $role, mixed $locationId): ?int
    {
        if ($role !== UserRole::GURU_PIC->value) {
            return null;
        }

        return $locationId !== null && $locationId !== '' ? (int) $locationId : null;
    }

    private function assertGuruPicLocationRules(string $role, mixed $locationId, ?int $ignoreUserId = null): void
    {
        if ($role !== UserRole::GURU_PIC->value) {
            return;
        }

        if ($locationId === null || $locationId === '') {
            throw ValidationException::withMessages([
                'location_id' => 'Lokasi penerima wajib diisi untuk peran Guru PIC.',
            ]);
        }

        $exists = User::query()
            ->where('role', UserRole::GURU_PIC->value)
            ->where('location_id', (int) $locationId)
            ->where('is_active', true)
            ->when($ignoreUserId, fn ($q) => $q->where('id', '!=', $ignoreUserId))
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'location_id' => 'Lokasi ini sudah memiliki Guru PIC aktif. Nonaktifkan PIC lama terlebih dahulu.',
            ]);
        }
    }
}
