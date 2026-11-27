<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Models\Location;
use App\Models\User;
use App\Services\UserManagementService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserController extends Controller
{
    public function __construct(
        private readonly UserManagementService $userManagement,
    ) {}

    /**
     * Daftar pengguna, filter, dan matriks hak akses read-only.
     */
    public function index(Request $request): View
    {
        $q = trim((string) $request->query('q', ''));
        $role = (string) $request->query('role', 'all');
        $status = (string) $request->query('status', 'all');
        $locationId = $request->query('location_id');

        $query = User::query()->with('location')->orderBy('name');

        if ($q !== '') {
            $query->where(function ($builder) use ($q): void {
                $builder->where('name', 'like', "%{$q}%")
                    ->orWhere('username', 'like', "%{$q}%")
                    ->orWhere('email', 'like', "%{$q}%");
            });
        }

        if ($role !== 'all' && $role !== '') {
            $query->where('role', $role);
        }

        if ($status === 'aktif') {
            $query->where('is_active', true);
        } elseif ($status === 'nonaktif') {
            $query->where('is_active', false);
        } elseif ($status === 'belum_login') {
            $query->whereNull('last_login_at');
        }

        if ($locationId !== null && $locationId !== '' && $locationId !== 'all') {
            $query->where('location_id', (int) $locationId);
        }

        $users = $query->paginate(10)->withQueryString();

        $allUsers = User::query()->get(['id', 'role', 'is_active', 'last_login_at']);
        $stats = [
            'aktif' => $allUsers->where('is_active', true)->count(),
            'nonaktif' => $allUsers->where('is_active', false)->count(),
            'belum_login' => $allUsers->whereNull('last_login_at')->count(),
            'peran_terpakai' => $allUsers->where('is_active', true)->pluck('role')->unique()->count(),
            'peran_total' => count(UserRole::cases()),
        ];

        return view('admin.users.index', [
            'users' => $users,
            'stats' => $stats,
            'roles' => UserRole::cases(),
            'locations' => Location::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'roleMatrix' => config('mbg.role_matrix', []),
            'filters' => [
                'q' => $q,
                'role' => $role,
                'status' => $status,
                'location_id' => $locationId ?? 'all',
            ],
        ]);
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $result = $this->userManagement->create($request->validated());

        return redirect()
            ->route('admin.users.index')
            ->with('success', "Akun {$result['user']->name} berhasil ditambahkan. Berikan kata sandi sementara kepada pengguna lalu minta mengganti saat login pertama.")
            ->with('temporary_password', $result['temporary_password'])
            ->with('temporary_password_user', $result['user']->username);
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $updated = $this->userManagement->update($user, $request->validated());

        return redirect()
            ->route('admin.users.index')
            ->with('success', "Profil akun {$updated->name} berhasil diperbarui.");
    }

    public function deactivate(Request $request, User $user): RedirectResponse
    {
        $reason = $request->validate([
            'reason' => ['nullable', 'string', 'max:500'],
        ])['reason'] ?? null;

        $this->userManagement->deactivate($user, $request->user(), $reason);

        return redirect()
            ->route('admin.users.index')
            ->with('success', "Akun {$user->name} dinonaktifkan. Riwayat aktivitas tetap tersimpan.");
    }

    public function activate(Request $request, User $user): RedirectResponse
    {
        $this->userManagement->activate($user, $request->user());

        return redirect()
            ->route('admin.users.index')
            ->with('success', "Akun {$user->name} diaktifkan kembali.");
    }

    public function resetPassword(Request $request, User $user): RedirectResponse
    {
        $reason = $request->validate([
            'reason' => ['nullable', 'string', 'max:500'],
        ])['reason'] ?? null;

        $result = $this->userManagement->resetPassword($user, $request->user(), $reason);

        return redirect()
            ->route('admin.users.index')
            ->with('success', "Kata sandi sementara untuk {$user->username} telah dibuat. Salin sekali ini lalu berikan kepada pengguna.")
            ->with('temporary_password', $result['temporary_password'])
            ->with('temporary_password_user', $user->username);
    }
}
