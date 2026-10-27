<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\ActivityLog;
use App\Models\BeneficiaryInstitution;
use App\Models\SopConfiguration;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class RbacManagementController extends Controller
{
    public function index(Request $request): View
    {
        $searchUser = trim((string) $request->query('search_user', ''));
        $roleFilter = $request->query('role', 'all');
        $userStatus = $request->query('user_status', 'all');

        $searchInst = trim((string) $request->query('search_inst', ''));
        $instType = $request->query('inst_type', 'all');

        // 1. Filter Personel 7 Role RBAC
        $userQuery = User::with('institution');
        if ($searchUser !== '') {
            $userQuery->where(function ($q) use ($searchUser) {
                $q->where('name', 'like', "%{$searchUser}%")
                  ->orWhere('email', 'like', "%{$searchUser}%")
                  ->orWhere('phone', 'like', "%{$searchUser}%");
            });
        }
        if ($roleFilter !== 'all') {
            $userQuery->where('role', $roleFilter);
        }
        if ($userStatus === 'active') {
            $userQuery->where('is_active', true);
        } elseif ($userStatus === 'inactive') {
            $userQuery->where('is_active', false);
        }
        $users = $userQuery->orderBy('id')->get();
        $allUsers = User::all();

        // 2. Filter Mitra Sekolah & Posyandu (5 Sekolah, 2 Posyandu = Total 1.414 Porsi)
        $instQuery = BeneficiaryInstitution::withCount('deliveries');
        if ($searchInst !== '') {
            $instQuery->where(function ($q) use ($searchInst) {
                $q->where('name', 'like', "%{$searchInst}%")
                  ->orWhere('code', 'like', "%{$searchInst}%")
                  ->orWhere('address', 'like', "%{$searchInst}%")
                  ->orWhere('pic_name', 'like', "%{$searchInst}%");
            });
        }
        if ($instType !== 'all') {
            $instQuery->where('type', $instType);
        }
        $institutions = $instQuery->orderBy('code')->get();
        $allInstitutions = BeneficiaryInstitution::orderBy('name')->get();
        $totalAllocatedQuota = $allInstitutions->sum('default_quota');

        // 3. Konfigurasi SOP & Parameter BGN
        $sopConfigs = SopConfiguration::with('updater')->orderBy('category')->get();

        // 4. Log Aktivitas Pengguna / Audit Trail (Fitur 2 & Kriteria Penerimaan)
        $auditLogs = ActivityLog::with('user')->latest('id')->take(40)->get();

        $roles = UserRole::cases();

        // 5. Data Grafik RBAC & Mitra
        $chartRoleLabels = collect($roles)->map(fn ($r) => $r->label());
        $chartRoleCounts = collect($roles)->map(fn ($r) => $allUsers->where('role', $r)->count());

        $chartInstLabels = $institutions->pluck('name');
        $chartInstQuotas = $institutions->pluck('default_quota');

        return view('users-rbac.index', compact(
            'users',
            'allUsers',
            'institutions',
            'allInstitutions',
            'totalAllocatedQuota',
            'sopConfigs',
            'auditLogs',
            'roles',
            'searchUser',
            'roleFilter',
            'userStatus',
            'searchInst',
            'instType',
            'chartRoleLabels',
            'chartRoleCounts',
            'chartInstLabels',
            'chartInstQuotas'
        ));
    }

    public function storeUser(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:25'],
            'role' => ['required', 'string'],
            'password' => ['required', Password::min(8)],
            'beneficiary_institution_id' => ['nullable', 'exists:beneficiary_institutions,id'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'role' => $validated['role'],
            'password' => Hash::make($validated['password']),
            'beneficiary_institution_id' => $validated['beneficiary_institution_id'],
            'is_active' => true,
        ]);

        ActivityLog::record(
            'create_user',
            'rbac',
            "Menambahkan akun pengguna baru {$user->name} ({$user->email}) dengan peran " . strtoupper($user->role->value) . "."
        );

        return redirect()->route('users-rbac.index')
            ->with('success', "Akun personel {$user->name} ({$user->role->label()}) berhasil didaftarkan ke sistem.");
    }

    public function updateUser(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email,' . $user->id],
            'phone' => ['nullable', 'string', 'max:25'],
            'role' => ['required', 'string'],
            'password' => ['nullable', Password::min(8)],
            'beneficiary_institution_id' => ['nullable', 'exists:beneficiary_institutions,id'],
        ]);

        $data = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'role' => $validated['role'],
            'beneficiary_institution_id' => $validated['beneficiary_institution_id'],
        ];

        if (! empty($validated['password'])) {
            $data['password'] = Hash::make($validated['password']);
        }

        $user->update($data);

        ActivityLog::record(
            'update_user',
            'rbac',
            "Memperbarui akun {$user->name} ({$user->email}) peran " . strtoupper($user->role->value) . "."
        );

        return redirect()->route('users-rbac.index')
            ->with('success', "Profil akun {$user->name} berhasil diperbarui.");
    }

    public function toggleUserStatus(User $user): RedirectResponse
    {
        if ($user->id === Auth::id()) {
            return back()->with('error', 'Anda tidak dapat menonaktifkan akun yang sedang aktif digunakan.');
        }

        $newStatus = ! $user->is_active;
        $user->update(['is_active' => $newStatus]);

        ActivityLog::record(
            'toggle_user_status',
            'rbac',
            ($newStatus ? 'Mengaktifkan kembali' : 'Menonaktifkan') . " akun {$user->name} ({$user->email})."
        );

        return redirect()->route('users-rbac.index')
            ->with('success', "Status akun {$user->name} berhasil diubah menjadi " . ($newStatus ? 'AKTIF' : 'NONAKTIF') . ".");
    }

    public function destroyUser(User $user): RedirectResponse
    {
        if ($user->id === Auth::id()) {
            return back()->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        $name = $user->name;
        $user->delete();

        ActivityLog::record(
            'delete_user',
            'rbac',
            "Menghapus akun {$name} dari basis data pengguna SPPG."
        );

        return redirect()->route('users-rbac.index')
            ->with('success', "Akun {$name} telah dihapus dari sistem.");
    }

    public function storeInstitution(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:20', 'unique:beneficiary_institutions,code'],
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', 'max:40'],
            'recipient_category' => ['required', 'in:siswa,balita_bumil,balita_busui,balita,ibu_hamil,ibu_menyusui,umum'],
            'default_quota' => ['required', 'integer', 'min:1'],
            'address' => ['required', 'string'],
            'contact_phone' => ['nullable', 'string', 'max:25'],
            'pic_name' => ['nullable', 'string', 'max:100'],
            'pic_phone' => ['nullable', 'string', 'max:25'],
        ]);

        $institution = BeneficiaryInstitution::create($validated);

        ActivityLog::record(
            'create_institution',
            'master_data',
            "Menambahkan mitra penerima baru {$institution->code} - {$institution->name} (Kuota: {$institution->default_quota} porsi)."
        );

        return redirect()->route('users-rbac.index')
            ->with('success', "Mitra {$institution->name} (Kuota: {$institution->default_quota} porsi) berhasil ditambahkan.");
    }

    public function updateInstitution(Request $request, BeneficiaryInstitution $institution): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', 'max:40'],
            'recipient_category' => ['required', 'in:siswa,balita_bumil,balita_busui,balita,ibu_hamil,ibu_menyusui,umum'],
            'default_quota' => ['required', 'integer', 'min:1'],
            'address' => ['required', 'string'],
            'contact_phone' => ['nullable', 'string', 'max:25'],
            'pic_name' => ['nullable', 'string', 'max:100'],
            'pic_phone' => ['nullable', 'string', 'max:25'],
        ]);

        $institution->update($validated);

        ActivityLog::record(
            'update_institution',
            'master_data',
            "Memperbarui data mitra {$institution->code} - {$institution->name} (Kuota: {$institution->default_quota} porsi)."
        );

        return redirect()->route('users-rbac.index')
            ->with('success', "Data mitra {$institution->name} berhasil diperbarui.");
    }

    public function toggleInstitution(BeneficiaryInstitution $institution): RedirectResponse
    {
        $newStatus = ! $institution->is_active;
        $institution->update(['is_active' => $newStatus]);

        ActivityLog::record(
            'toggle_institution',
            'master_data',
            ($newStatus ? 'Mengaktifkan kembali' : 'Menonaktifkan sementara') . " distribusi mitra {$institution->name}."
        );

        return redirect()->route('users-rbac.index')
            ->with('success', "Status keaktifan mitra {$institution->name} berhasil diperbarui.");
    }

    public function destroyInstitution(BeneficiaryInstitution $institution): RedirectResponse
    {
        $name = $institution->name;
        $institution->delete();

        ActivityLog::record(
            'delete_institution',
            'master_data',
            "Menghapus mitra {$name} dari master data penerima manfaat."
        );

        return redirect()->route('users-rbac.index')
            ->with('success', "Mitra {$name} telah dihapus dari sistem.");
    }

    /**
     * Pembaruan Parameter Konfigurasi SOP BGN & Pagu Anggaran (Fitur 3)
     */
    public function updateSop(Request $request, SopConfiguration $sop): RedirectResponse
    {
        $validated = $request->validate([
            'param_value' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:500'],
        ]);

        $sop->update([
            'param_value' => $validated['param_value'],
            'description' => $validated['description'] ?? $sop->description,
            'updated_by' => Auth::id(),
        ]);

        ActivityLog::record(
            'update_sop',
            'sop_config',
            "Memperbarui parameter SOP {$sop->param_name}: {$sop->param_value} {$sop->unit}."
        );

        return redirect()->route('users-rbac.index')
            ->with('success', "Parameter SOP {$sop->param_name} berhasil diperbarui menjadi {$sop->param_value} {$sop->unit}.");
    }
}
