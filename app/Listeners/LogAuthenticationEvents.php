<?php

namespace App\Listeners;

use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;

class LogAuthenticationEvents
{
    public function handleLogin(Login $event): void
    {
        /** @var User $user */
        $user = $event->user;

        AuditLogService::record(
            action: 'login',
            module: 'Autentikasi',
            target: $user->username ?? $user->email,
            actor: $user,
        );
    }

    public function handleLogout(Logout $event): void
    {
        if (! $event->user instanceof User) {
            return;
        }

        AuditLogService::record(
            action: 'logout',
            module: 'Autentikasi',
            target: $event->user->username ?? $event->user->email,
            actor: $event->user,
        );
    }

    public function handleFailed(Failed $event): void
    {
        $identifier = (string) ($event->credentials['username'] ?? $event->credentials['email'] ?? 'tidak-diketahui');
        $user = $event->user instanceof User ? $event->user : null;

        AuditLogService::record(
            action: 'login_gagal',
            module: 'Autentikasi',
            target: $identifier,
            oldValues: null,
            newValues: ['identifier' => $identifier],
            reason: 'Percobaan masuk gagal',
            actor: $user,
        );
    }
}
