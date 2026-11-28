<?php

namespace Database\Seeders;

use App\Enums\MenuStatus;
use App\Enums\PaguCheckResult;
use App\Models\AuditLog;
use App\Models\DailyMenu;
use App\Models\Ingredient;
use App\Models\MenuCategoryPortion;
use App\Models\MenuComponent;
use App\Models\MenuHistory;
use App\Models\PaguCheck;
use App\Models\PaguCheckEstimate;
use App\Models\PaguParameter;
use App\Models\PaguParameterHistory;
use App\Models\RecipientCategory;
use App\Models\User;
use App\Services\AuditLogService;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AkuntanSeeder extends Seeder
{
    public function run(): void
    {
        AuditLogService::$enabled = false;

        try {
            $nadia = User::where('username', 'nadia.putri')->firstOrFail();
            $rizky = User::where('username', 'rizky.pratama')->firstOrFail();
            $hendra = User::where('username', 'hendra.wijaya')->firstOrFail();
            $dimas = User::where('username', 'dimas.anwar')->first()
                ?? User::where('username', 'arief.mahendra')->firstOrFail();

            $categories = RecipientCategory::query()->get()->keyBy('name');
            $ingredients = Ingredient::query()->pluck('id', 'name');

            $this->seedPagu($categories, $rizky, $dimas);
            $this->seedMenus($categories, $ingredients, $nadia, $rizky, $hendra);
            $this->seedAuditActivities($rizky);
        } finally {
            AuditLogService::$enabled = true;
        }
    }

    /**
     * @param  Collection<string, RecipientCategory>  $categories
     */
    private function seedPagu($categories, User $rizky, User $dimas): void
    {
        PaguParameterHistory::query()->delete();

        $specs = [
            'Siswa' => [
                'nominal' => 15000,
                'effective' => '2026-11-21',
                'by' => $rizky,
                'history' => [
                    ['lama' => null, 'baru' => 14000, 'at' => '2026-11-01 09:00:00', 'by' => $dimas, 'catatan' => 'Parameter awal ditetapkan'],
                    ['lama' => 14000, 'baru' => 15000, 'at' => '2026-11-21 10:00:00', 'by' => $rizky, 'catatan' => 'Penyesuaian batas PAGU Siswa'],
                ],
            ],
            'Ibu hamil' => [
                'nominal' => 18000,
                'effective' => '2026-11-01',
                'by' => $dimas,
                'history' => [
                    ['lama' => null, 'baru' => 18000, 'at' => '2026-11-01 09:00:00', 'by' => $dimas, 'catatan' => 'Parameter awal ditetapkan'],
                ],
            ],
            'Ibu menyusui' => [
                'nominal' => 18000,
                'effective' => '2026-11-01',
                'by' => $dimas,
                'history' => [
                    ['lama' => null, 'baru' => 18000, 'at' => '2026-11-01 09:00:00', 'by' => $dimas, 'catatan' => 'Parameter awal ditetapkan'],
                ],
            ],
            'Balita' => [
                'nominal' => 12000,
                'effective' => '2026-11-21',
                'by' => $rizky,
                'history' => [
                    ['lama' => null, 'baru' => 11000, 'at' => '2026-11-01 09:00:00', 'by' => $dimas, 'catatan' => 'Parameter awal ditetapkan'],
                    ['lama' => 11000, 'baru' => 12000, 'at' => '2026-11-21 10:05:00', 'by' => $rizky, 'catatan' => 'Penyesuaian batas PAGU Balita'],
                ],
            ],
        ];

        foreach ($specs as $name => $spec) {
            $cat = $categories[$name];
            PaguParameter::updateOrCreate(
                ['recipient_category_id' => $cat->id],
                [
                    'max_budget_per_portion' => $spec['nominal'],
                    'effective_date' => $spec['effective'],
                    'notes' => 'Batas PAGU per porsi',
                    'is_active' => true,
                    'updated_by' => $spec['by']->id,
                ]
            );

            foreach ($spec['history'] as $row) {
                PaguParameterHistory::query()->create([
                    'recipient_category_id' => $cat->id,
                    'nilai_lama' => $row['lama'],
                    'nilai_baru' => $row['baru'],
                    'user_id' => $row['by']->id,
                    'catatan' => $row['catatan'],
                    'created_at' => Carbon::parse($row['at'], 'Asia/Jakarta'),
                ]);
            }
        }
    }

    /**
     * @param  Collection<string, RecipientCategory>  $categories
     * @param  Collection<string, int>  $ingredients
     */
    private function seedMenus($categories, $ingredients, User $nadia, User $rizky, User $hendra): void
    {
        $allFour = [
            'Siswa' => 1284,
            'Ibu hamil' => 30,
            'Ibu menyusui' => 30,
            'Balita' => 70,
        ];

        $menus = [
            [
                'code' => 'MNU-SS1-01',
                'date' => '2026-11-26',
                'name' => 'Nasi, ayam goreng, tumis buncis, tahu, susu',
                'portions' => $allFour,
                'status' => MenuStatus::DISETUJUI,
                'components' => ['Beras', 'Ayam Fillet', 'Buncis', 'Tahu Putih', 'Susu UHT'],
                'submitted_at' => '2026-11-25 08:50:00',
                'cycle' => 1,
                'check' => [
                    'hasil' => PaguCheckResult::LOLOS,
                    'at' => '2026-11-25 10:20:00',
                    'catatan' => 'Sesuai batas PAGU.',
                ],
                'approved_at' => '2026-11-25 13:40:00',
                'approved_note' => 'Siap produksi pagi',
                'last_note' => 'Kepala Dapur: Siap produksi pagi',
            ],
            [
                'code' => 'MNU-SS1-02',
                'date' => '2026-11-27',
                'name' => 'Nasi, ikan patin bakar, sayur bayam, tempe, susu',
                'portions' => $allFour,
                'status' => MenuStatus::DISETUJUI,
                'components' => ['Beras', 'Ikan Patin', 'Bayam', 'Tempe', 'Susu UHT'],
                'submitted_at' => '2026-11-26 06:30:00',
                'cycle' => 1,
                'check' => [
                    'hasil' => PaguCheckResult::LOLOS,
                    'at' => '2026-11-26 07:10:00',
                    'catatan' => 'Sesuai batas PAGU.',
                ],
                'approved_at' => '2026-11-26 07:50:00',
                'approved_note' => 'Siap produksi 27 Nov',
                'last_note' => 'Kepala Dapur: Siap produksi 27 Nov',
            ],
            [
                'code' => 'MNU-SS1-03',
                'date' => '2026-11-28',
                'name' => 'Nasi, telur balado, sayur wortel buncis, tahu',
                'portions' => ['Siswa' => 1284, 'Balita' => 70],
                'status' => MenuStatus::MENUNGGU_PEMERIKSAAN_ANGGARAN,
                'components' => ['Beras', 'Telur Ayam', 'Wortel', 'Buncis', 'Tahu Putih'],
                'submitted_at' => '2026-11-26 06:45:00',
                'cycle' => 1,
                'check' => null,
                'approved_at' => null,
                'approved_note' => null,
                'last_note' => 'Ahli Gizi: Diajukan ke Akuntan (26 Nov 06:45 WIB, Nadia Putri)',
            ],
            [
                'code' => 'MNU-SS1-04',
                'date' => '2026-11-29',
                'name' => 'Nasi, ayam kecap, capcay wortel, tahu goreng',
                'portions' => $allFour,
                'status' => MenuStatus::MINTA_REVISI,
                'components' => ['Beras', 'Ayam Fillet', 'Wortel', 'Tahu Putih'],
                'submitted_at' => '2026-11-25 09:30:00',
                'cycle' => 1,
                'check' => [
                    'hasil' => PaguCheckResult::LOLOS,
                    'at' => '2026-11-25 11:05:00',
                    'catatan' => 'Sesuai batas PAGU.',
                ],
                'approved_at' => null,
                'kitchen_revision_at' => '2026-11-25 14:20:00',
                'approved_note' => 'Ganti lauk hewani, stok ayam terbatas',
                'last_note' => 'Kepala Dapur: Ganti lauk hewani, stok ayam terbatas',
            ],
            [
                'code' => 'MNU-SS1-05',
                'date' => '2026-11-30',
                'name' => 'Nasi, rendang daging, tumis bayam, tempe, susu',
                'portions' => ['Siswa' => 1284],
                'status' => MenuStatus::PERLU_REVISI_ANGGARAN,
                'components' => ['Beras', 'Daging Sapi', 'Bayam', 'Tempe', 'Susu UHT'],
                'submitted_at' => '2026-11-25 13:10:00',
                'cycle' => 1,
                'check' => [
                    'hasil' => PaguCheckResult::PERLU_REVISI,
                    'at' => '2026-11-25 15:40:00',
                    'catatan' => 'Melebihi batas PAGU kategori Siswa.',
                    'estimates' => [
                        'Siswa' => ['estimasi' => 17500, 'pagu' => 15000],
                    ],
                ],
                'approved_at' => null,
                'approved_note' => null,
                'last_note' => 'Akuntan: Melebihi batas PAGU kategori Siswa.',
            ],
            [
                'code' => 'MNU-SS1-06',
                'date' => '2026-12-01',
                'name' => 'Nasi, ikan patin goreng, tumis buncis, tahu',
                'portions' => ['Siswa' => 1284, 'Ibu hamil' => 30, 'Balita' => 70],
                'status' => MenuStatus::DRAF,
                'components' => ['Beras', 'Ikan Patin', 'Buncis', 'Tahu Putih'],
                'submitted_at' => null,
                'cycle' => 0,
                'check' => null,
                'approved_at' => null,
                'approved_note' => null,
                'last_note' => 'Draf (belum diajukan)',
            ],
        ];

        foreach ($menus as $spec) {
            $total = array_sum($spec['portions']);
            $lockedAt = $spec['status'] === MenuStatus::DISETUJUI
                ? Carbon::parse($spec['approved_at'], 'Asia/Jakarta')
                : null;

            $menu = DailyMenu::updateOrCreate(
                ['menu_code' => $spec['code']],
                [
                    'operational_date' => $spec['date'],
                    'menu_name' => $spec['name'],
                    'target_portions' => $total,
                    'recipient_category' => 'siswa',
                    'categories_json' => array_keys($spec['portions']),
                    'components_json' => $spec['components'],
                    'calories_kcal' => 0,
                    'protein_gram' => 0,
                    'carbs_gram' => 0,
                    'fat_gram' => 0,
                    'fiber_gram' => 0,
                    'estimated_cost_per_portion' => 0,
                    'pagu_status' => match ($spec['status']) {
                        MenuStatus::PERLU_REVISI_ANGGARAN => 'melebihi_pagu',
                        MenuStatus::DISETUJUI, MenuStatus::LOLOS_PAGU, MenuStatus::MINTA_REVISI => 'sesuai_pagu',
                        default => 'pending',
                    },
                    'pagu_notes' => $spec['check']['catatan'] ?? null,
                    'verified_by_accountant_id' => isset($spec['check']) ? $rizky->id : null,
                    'pagu_verified_at' => isset($spec['check'])
                        ? Carbon::parse($spec['check']['at'], 'Asia/Jakarta')
                        : null,
                    'status' => $spec['status']->value,
                    'created_by' => $nadia->id,
                    'submitted_by' => $spec['submitted_at'] ? $nadia->id : null,
                    'submitted_at' => $spec['submitted_at']
                        ? Carbon::parse($spec['submitted_at'], 'Asia/Jakarta')
                        : null,
                    'approved_by' => $spec['approved_at'] ? $hendra->id : null,
                    'approved_at' => $spec['approved_at']
                        ? Carbon::parse($spec['approved_at'], 'Asia/Jakarta')
                        : null,
                    'approval_notes' => $spec['last_note'],
                    'last_note' => $spec['last_note'],
                    'locked_at' => $lockedAt,
                    'submission_cycle' => $spec['cycle'],
                    'revision_reason' => $spec['status'] === MenuStatus::MINTA_REVISI
                        ? ($spec['approved_note'] ?? null)
                        : null,
                ]
            );

            MenuCategoryPortion::query()->where('daily_menu_id', $menu->id)->delete();
            foreach ($spec['portions'] as $catName => $qty) {
                MenuCategoryPortion::query()->create([
                    'daily_menu_id' => $menu->id,
                    'recipient_category_id' => $categories[$catName]->id,
                    'portions' => $qty,
                ]);
            }

            MenuComponent::query()->where('daily_menu_id', $menu->id)->delete();
            foreach ($spec['components'] as $ingredientName) {
                if (! isset($ingredients[$ingredientName])) {
                    continue;
                }
                MenuComponent::query()->create([
                    'daily_menu_id' => $menu->id,
                    'ingredient_id' => $ingredients[$ingredientName],
                ]);
            }

            MenuHistory::query()->where('daily_menu_id', $menu->id)->delete();
            PaguCheck::query()->where('daily_menu_id', $menu->id)->each(function (PaguCheck $check): void {
                PaguCheckEstimate::query()->where('pagu_check_id', $check->id)->delete();
                DB::table('pagu_checks')->where('id', $check->id)->delete();
            });

            if ($spec['submitted_at']) {
                MenuHistory::query()->create([
                    'daily_menu_id' => $menu->id,
                    'aksi' => 'mengajukan_pemeriksaan_anggaran',
                    'user_id' => $nadia->id,
                    'catatan' => 'Diajukan ke Akuntan untuk pemeriksaan PAGU.',
                    'created_at' => Carbon::parse($spec['submitted_at'], 'Asia/Jakarta'),
                ]);
            }

            if ($spec['check']) {
                $checkId = DB::table('pagu_checks')->insertGetId([
                    'daily_menu_id' => $menu->id,
                    'siklus_pengajuan' => $spec['cycle'],
                    'user_id' => $rizky->id,
                    'hasil' => $spec['check']['hasil']->value,
                    'catatan' => $spec['check']['catatan'],
                    'checked_at' => Carbon::parse($spec['check']['at'], 'Asia/Jakarta'),
                    'created_at' => Carbon::parse($spec['check']['at'], 'Asia/Jakarta'),
                    'updated_at' => Carbon::parse($spec['check']['at'], 'Asia/Jakarta'),
                ]);

                MenuHistory::query()->create([
                    'daily_menu_id' => $menu->id,
                    'aksi' => $spec['check']['hasil'] === PaguCheckResult::LOLOS
                        ? 'mencatat_lolos_pagu'
                        : 'mencatat_perlu_revisi_anggaran',
                    'user_id' => $rizky->id,
                    'catatan' => $spec['check']['catatan'],
                    'created_at' => Carbon::parse($spec['check']['at'], 'Asia/Jakarta'),
                ]);

                foreach ($spec['check']['estimates'] ?? [] as $catName => $est) {
                    PaguCheckEstimate::query()->create([
                        'pagu_check_id' => $checkId,
                        'recipient_category_id' => $categories[$catName]->id,
                        'estimasi_per_porsi' => $est['estimasi'],
                        'pagu_saat_itu' => $est['pagu'],
                    ]);
                }
            }

            if ($spec['approved_at']) {
                MenuHistory::query()->create([
                    'daily_menu_id' => $menu->id,
                    'aksi' => 'menyetujui_menu',
                    'user_id' => $hendra->id,
                    'catatan' => $spec['approved_note'],
                    'created_at' => Carbon::parse($spec['approved_at'], 'Asia/Jakarta'),
                ]);
            }

            if (! empty($spec['kitchen_revision_at'])) {
                MenuHistory::query()->create([
                    'daily_menu_id' => $menu->id,
                    'aksi' => 'meminta_revisi',
                    'user_id' => $hendra->id,
                    'catatan' => $spec['approved_note'],
                    'created_at' => Carbon::parse($spec['kitchen_revision_at'], 'Asia/Jakarta'),
                ]);
            }
        }
    }

    private function seedAuditActivities(User $rizky): void
    {
        $entries = [
            [
                'at' => '2026-11-26 07:10:00',
                'action' => 'Mencatat Lolos PAGU menu 27 Nov',
                'module' => 'Validasi Anggaran Menu',
                'target' => 'Menu 27 Nov 2026',
            ],
            [
                'at' => '2026-11-25 15:40:00',
                'action' => 'Mencatat Perlu Revisi Anggaran menu 30 Nov',
                'module' => 'Validasi Anggaran Menu',
                'target' => 'Menu 30 Nov 2026',
            ],
            [
                'at' => '2026-11-25 11:05:00',
                'action' => 'Mencatat Lolos PAGU menu 29 Nov',
                'module' => 'Validasi Anggaran Menu',
                'target' => 'Menu 29 Nov 2026',
            ],
            [
                'at' => '2026-11-25 10:20:00',
                'action' => 'Mencatat Lolos PAGU menu 26 Nov',
                'module' => 'Validasi Anggaran Menu',
                'target' => 'Menu 26 Nov 2026',
            ],
            [
                'at' => '2026-11-21 10:00:00',
                'action' => 'Memperbarui Parameter PAGU Siswa dan Balita',
                'module' => 'Parameter PAGU',
                'target' => 'Parameter PAGU · Siswa, Balita',
            ],
        ];

        foreach ($entries as $entry) {
            $exists = AuditLog::query()
                ->where('user_id', $rizky->id)
                ->where('action', $entry['action'])
                ->exists();

            if ($exists) {
                continue;
            }

            DB::table('audit_logs')->insert([
                'occurred_at' => Carbon::parse($entry['at'], 'Asia/Jakarta'),
                'user_id' => $rizky->id,
                'user_name_snapshot' => $rizky->name,
                'role' => 'Akuntan',
                'action' => $entry['action'],
                'target' => $entry['target'],
                'module' => $entry['module'],
                'ip_address' => '127.0.0.1',
                'old_values' => null,
                'new_values' => null,
                'reason' => null,
                'approver_id' => null,
                'created_at' => Carbon::parse($entry['at'], 'Asia/Jakarta'),
            ]);
        }
    }
}
