<?php

namespace App\Services;

use App\Enums\UserRole;
use App\Models\PaguParameter;
use App\Models\PaguParameterHistory;
use App\Models\RecipientCategory;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PaguParameterService
{
    public function updateNominal(
        RecipientCategory $category,
        int $nominal,
        User $actor,
        ?string $catatan = null,
    ): PaguParameter {
        if (! in_array($actor->role, [UserRole::AKUNTAN, UserRole::ADMIN], true)) {
            throw ValidationException::withMessages([
                'role' => 'Hanya Akuntan atau Administrator Sistem yang dapat mengubah Parameter PAGU.',
            ]);
        }

        if ($nominal <= 0) {
            throw ValidationException::withMessages([
                'nominal_per_porsi' => 'Nominal harus lebih dari 0',
            ]);
        }

        return DB::transaction(function () use ($category, $nominal, $actor, $catatan) {
            $parameter = PaguParameter::query()
                ->where('recipient_category_id', $category->id)
                ->where('is_active', true)
                ->orderByDesc('effective_date')
                ->orderByDesc('id')
                ->lockForUpdate()
                ->first();

            $oldValue = $parameter?->max_budget_per_portion;

            if ($parameter) {
                $parameter->forceFill([
                    'max_budget_per_portion' => $nominal,
                    'effective_date' => now('Asia/Jakarta')->toDateString(),
                    'updated_by' => $actor->id,
                    'notes' => $catatan,
                ])->save();
            } else {
                $parameter = PaguParameter::query()->create([
                    'recipient_category_id' => $category->id,
                    'max_budget_per_portion' => $nominal,
                    'effective_date' => now('Asia/Jakarta')->toDateString(),
                    'notes' => $catatan,
                    'is_active' => true,
                    'updated_by' => $actor->id,
                ]);
            }

            PaguParameterHistory::query()->create([
                'recipient_category_id' => $category->id,
                'nilai_lama' => $oldValue,
                'nilai_baru' => $nominal,
                'user_id' => $actor->id,
                'catatan' => $catatan,
                'created_at' => now(),
            ]);

            AuditLogService::record(
                action: 'Memperbarui Parameter PAGU '.$category->name,
                module: 'Parameter PAGU',
                target: 'Parameter PAGU · '.$category->name,
                oldValues: ['nominal_per_porsi' => $oldValue],
                newValues: ['nominal_per_porsi' => $nominal],
                reason: $catatan,
                actor: $actor,
            );

            return $parameter->fresh('recipientCategory');
        });
    }
}
