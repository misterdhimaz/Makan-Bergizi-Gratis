<?php

namespace App\Services;

use App\Enums\MenuStatus;
use App\Enums\PaguCheckResult;
use App\Enums\UserRole;
use App\Exceptions\MenuStatusConflictException;
use App\Models\DailyMenu;
use App\Models\MenuHistory;
use App\Models\PaguCheck;
use App\Models\PaguCheckEstimate;
use App\Models\PaguParameter;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class MenuWorkflowService
{
    /**
     * Ahli Gizi mengajukan draf / hasil revisi ke Akuntan.
     */
    public function submitForBudgetReview(DailyMenu $menu, User $actor, ?string $catatan = null): DailyMenu
    {
        $this->assertRole($actor, [UserRole::AHLI_GIZI]);

        return DB::transaction(function () use ($menu, $actor, $catatan) {
            $locked = DailyMenu::query()->whereKey($menu->id)->lockForUpdate()->firstOrFail();
            $status = MenuStatus::fromLegacy((string) $locked->status);

            if (! in_array($status, [
                MenuStatus::DRAF,
                MenuStatus::PERLU_REVISI_ANGGARAN,
                MenuStatus::MINTA_REVISI,
            ], true)) {
                throw new MenuStatusConflictException;
            }

            $cycle = (int) $locked->submission_cycle + 1;
            $note = $catatan ?: 'Diajukan ke Akuntan untuk pemeriksaan PAGU.';

            $locked->forceFill([
                'status' => MenuStatus::MENUNGGU_PEMERIKSAAN_ANGGARAN->value,
                'submitted_by' => $actor->id,
                'submitted_at' => now(),
                'submission_cycle' => $cycle,
                'last_note' => 'Ahli Gizi: '.$note,
                'pagu_status' => 'pending',
                'approval_notes' => 'Ahli Gizi: Diajukan ke Akuntan',
            ])->save();

            $this->writeHistory($locked, 'mengajukan_pemeriksaan_anggaran', $actor, $note);
            AuditLogService::record(
                action: 'Mengajukan menu ke pemeriksaan PAGU',
                module: 'Perencanaan Menu',
                target: 'Menu '.$locked->operational_date?->timezone('Asia/Jakarta')->translatedFormat('j M Y'),
                newValues: ['status' => $locked->status, 'siklus' => $cycle],
                actor: $actor,
            );

            return $locked->fresh();
        });
    }

    /**
     * Akuntan mencatat hasil pemeriksaan PAGU (sekali per siklus).
     *
     * @param  array<int, array{recipient_category_id:int, estimasi_per_porsi:?int}>  $estimates
     */
    public function recordPaguCheck(
        DailyMenu $menu,
        User $actor,
        PaguCheckResult $hasil,
        string $catatan,
        array $estimates = [],
    ): PaguCheck {
        $this->assertRole($actor, [UserRole::AKUNTAN]);

        $catatan = trim($catatan);
        if ($hasil === PaguCheckResult::PERLU_REVISI && mb_strlen($catatan) < 5) {
            throw ValidationException::withMessages([
                'catatan' => 'Catatan wajib diisi minimal 5 karakter untuk hasil Perlu Revisi.',
            ]);
        }

        if ($hasil === PaguCheckResult::LOLOS && $catatan === '') {
            $catatan = 'Sesuai batas PAGU.';
        }

        return DB::transaction(function () use ($menu, $actor, $hasil, $catatan, $estimates) {
            $locked = DailyMenu::query()->whereKey($menu->id)->lockForUpdate()->firstOrFail();

            if (MenuStatus::fromLegacy((string) $locked->status) !== MenuStatus::MENUNGGU_PEMERIKSAAN_ANGGARAN) {
                throw new MenuStatusConflictException;
            }

            $cycle = max(1, (int) $locked->submission_cycle);

            if (PaguCheck::query()->where('daily_menu_id', $locked->id)->where('siklus_pengajuan', $cycle)->exists()) {
                throw new MenuStatusConflictException('Pemeriksaan untuk siklus ini sudah dicatat.');
            }

            $newStatus = $hasil->resultingMenuStatus();

            $check = PaguCheck::query()->create([
                'daily_menu_id' => $locked->id,
                'siklus_pengajuan' => $cycle,
                'user_id' => $actor->id,
                'hasil' => $hasil->value,
                'catatan' => $catatan,
                'checked_at' => now(),
            ]);

            $this->storeEstimates($check, $estimates);

            $locked->forceFill([
                'status' => $newStatus->value,
                'pagu_status' => $hasil === PaguCheckResult::LOLOS ? 'sesuai_pagu' : 'melebihi_pagu',
                'pagu_notes' => $catatan,
                'verified_by_accountant_id' => $actor->id,
                'pagu_verified_at' => now(),
                'last_note' => 'Akuntan: '.$catatan,
                'approval_notes' => 'Akuntan: '.$catatan,
            ])->save();

            $aksi = $hasil === PaguCheckResult::LOLOS ? 'mencatat_lolos_pagu' : 'mencatat_perlu_revisi_anggaran';
            $auditAction = $hasil === PaguCheckResult::LOLOS
                ? 'Mencatat Lolos PAGU menu '.$locked->operational_date?->timezone('Asia/Jakarta')->translatedFormat('j M Y')
                : 'Mencatat Perlu Revisi Anggaran menu '.$locked->operational_date?->timezone('Asia/Jakarta')->translatedFormat('j M Y');

            $this->writeHistory($locked, $aksi, $actor, $catatan);
            AuditLogService::record(
                action: $auditAction,
                module: 'Validasi Anggaran Menu',
                target: 'Menu '.$locked->operational_date?->timezone('Asia/Jakarta')->translatedFormat('j M Y'),
                newValues: [
                    'hasil' => $hasil->value,
                    'status' => $newStatus->value,
                    'siklus' => $cycle,
                ],
                actor: $actor,
            );

            return $check->fresh(['estimates.recipientCategory']);
        });
    }

    /**
     * Kepala Dapur menyetujui menu yang sudah lolos PAGU (mengunci).
     */
    public function approveByKepalaDapur(DailyMenu $menu, User $actor, ?string $catatan = null): DailyMenu
    {
        $this->assertRole($actor, [UserRole::KEPALA_DAPUR]);

        return DB::transaction(function () use ($menu, $actor, $catatan) {
            $locked = DailyMenu::query()->whereKey($menu->id)->lockForUpdate()->firstOrFail();

            if (MenuStatus::fromLegacy((string) $locked->status) !== MenuStatus::LOLOS_PAGU) {
                throw new MenuStatusConflictException;
            }

            $note = $catatan ?: 'Siap produksi.';

            $locked->forceFill([
                'status' => MenuStatus::DISETUJUI->value,
                'approved_by' => $actor->id,
                'approved_at' => now(),
                'locked_at' => now(),
                'last_note' => 'Kepala Dapur: '.$note,
                'approval_notes' => 'Kepala Dapur: '.$note,
            ])->save();

            $this->writeHistory($locked, 'menyetujui_menu', $actor, $note);
            AuditLogService::record(
                action: 'Menyetujui menu '.$locked->operational_date?->timezone('Asia/Jakarta')->translatedFormat('j M Y'),
                module: 'Persetujuan Menu',
                target: 'Menu '.$locked->operational_date?->timezone('Asia/Jakarta')->translatedFormat('j M Y'),
                newValues: ['status' => MenuStatus::DISETUJUI->value],
                actor: $actor,
            );

            return $locked->fresh();
        });
    }

    /**
     * Kepala Dapur meminta revisi menu yang sudah lolos PAGU.
     */
    public function requestRevisionByKepalaDapur(DailyMenu $menu, User $actor, string $catatan): DailyMenu
    {
        $this->assertRole($actor, [UserRole::KEPALA_DAPUR]);

        $catatan = trim($catatan);
        if (mb_strlen($catatan) < 5) {
            throw ValidationException::withMessages([
                'catatan' => 'Catatan wajib diisi minimal 5 karakter.',
            ]);
        }

        return DB::transaction(function () use ($menu, $actor, $catatan) {
            $locked = DailyMenu::query()->whereKey($menu->id)->lockForUpdate()->firstOrFail();

            if (MenuStatus::fromLegacy((string) $locked->status) !== MenuStatus::LOLOS_PAGU) {
                throw new MenuStatusConflictException;
            }

            $locked->forceFill([
                'status' => MenuStatus::MINTA_REVISI->value,
                'last_note' => 'Kepala Dapur: '.$catatan,
                'approval_notes' => 'Kepala Dapur: '.$catatan,
                'revision_reason' => $catatan,
            ])->save();

            $this->writeHistory($locked, 'meminta_revisi', $actor, $catatan);
            AuditLogService::record(
                action: 'Meminta revisi menu '.$locked->operational_date?->timezone('Asia/Jakarta')->translatedFormat('j M Y'),
                module: 'Persetujuan Menu',
                target: 'Menu '.$locked->operational_date?->timezone('Asia/Jakarta')->translatedFormat('j M Y'),
                newValues: ['status' => MenuStatus::MINTA_REVISI->value],
                reason: $catatan,
                actor: $actor,
            );

            return $locked->fresh();
        });
    }

    /**
     * Tolak setiap transisi yang tidak diizinkan (untuk unit test & guard).
     */
    public function assertTransitionAllowed(MenuStatus $from, MenuStatus $to, UserRole $role): void
    {
        $allowed = match ($from) {
            MenuStatus::DRAF => $to === MenuStatus::MENUNGGU_PEMERIKSAAN_ANGGARAN && $role === UserRole::AHLI_GIZI,
            MenuStatus::MENUNGGU_PEMERIKSAAN_ANGGARAN => in_array($to, [MenuStatus::LOLOS_PAGU, MenuStatus::PERLU_REVISI_ANGGARAN], true)
                && $role === UserRole::AKUNTAN,
            MenuStatus::PERLU_REVISI_ANGGARAN, MenuStatus::MINTA_REVISI => $to === MenuStatus::MENUNGGU_PEMERIKSAAN_ANGGARAN
                && $role === UserRole::AHLI_GIZI,
            MenuStatus::LOLOS_PAGU => in_array($to, [MenuStatus::DISETUJUI, MenuStatus::MINTA_REVISI], true)
                && $role === UserRole::KEPALA_DAPUR,
            default => false,
        };

        if (! $allowed) {
            throw new MenuStatusConflictException('Transisi status tidak diizinkan.');
        }
    }

    /**
     * @param  array<int, array{recipient_category_id:int, estimasi_per_porsi:?int}>  $estimates
     */
    private function storeEstimates(PaguCheck $check, array $estimates): void
    {
        foreach ($estimates as $row) {
            $categoryId = (int) ($row['recipient_category_id'] ?? 0);
            if ($categoryId <= 0) {
                continue;
            }

            $estimasi = $row['estimasi_per_porsi'] ?? null;
            if ($estimasi === '' || $estimasi === null) {
                $estimasi = null;
            } else {
                $estimasi = (int) $estimasi;
                if ($estimasi < 0) {
                    throw ValidationException::withMessages([
                        'estimates' => 'Estimasi biaya per porsi tidak boleh negatif.',
                    ]);
                }
            }

            $pagu = PaguParameter::query()
                ->where('recipient_category_id', $categoryId)
                ->where('is_active', true)
                ->orderByDesc('effective_date')
                ->orderByDesc('id')
                ->value('max_budget_per_portion');

            if ($pagu === null) {
                throw ValidationException::withMessages([
                    'estimates' => 'Parameter PAGU untuk kategori tidak ditemukan.',
                ]);
            }

            PaguCheckEstimate::query()->create([
                'pagu_check_id' => $check->id,
                'recipient_category_id' => $categoryId,
                'estimasi_per_porsi' => $estimasi,
                'pagu_saat_itu' => (int) $pagu,
            ]);
        }
    }

    private function writeHistory(DailyMenu $menu, string $aksi, User $actor, ?string $catatan): void
    {
        MenuHistory::query()->create([
            'daily_menu_id' => $menu->id,
            'aksi' => $aksi,
            'user_id' => $actor->id,
            'catatan' => $catatan,
            'created_at' => now(),
        ]);
    }

    /**
     * @param  list<UserRole>  $roles
     */
    private function assertRole(User $actor, array $roles): void
    {
        foreach ($roles as $role) {
            if ($actor->role === $role) {
                return;
            }
        }

        throw ValidationException::withMessages([
            'role' => 'Peran Anda tidak diizinkan melakukan aksi ini.',
        ]);
    }
}
