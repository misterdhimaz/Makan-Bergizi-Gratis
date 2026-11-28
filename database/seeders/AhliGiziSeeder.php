<?php

namespace Database\Seeders;

use App\Enums\IngredientCategory;
use App\Models\BeneficiaryInstitution;
use App\Models\ComplaintTicket;
use App\Models\DailyMenu;
use App\Models\DailyMenuItem;
use App\Models\FoodSafetyCheck;
use App\Models\FoodSafetyFinding;
use App\Models\Ingredient;
use App\Models\InventoryTransaction;
use App\Models\Location;
use App\Models\ProductionObstacle;
use App\Models\ProductionRun;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Database\Seeder;

class AhliGiziSeeder extends Seeder
{
    public function run(): void
    {
        AuditLogService::$enabled = false;

        try {
            $now = now('Asia/Jakarta');
            $today = $now->toDateString();

            $nadia = User::where('username', 'nadia.putri')->first();
            $hendra = User::where('username', 'hendra.wijaya')->first();
            $rizky = User::where('username', 'rizky.pratama')->first();
            $bima = User::where('username', 'bima.santoso')->first();
            $ahmad = User::where('username', 'ahmad.fauzi')->first();

            if (! $nadia || ! $hendra || ! $rizky || ! $bima) {
                return;
            }

            // Pastikan bahan tambahan (Daging Sapi & Tempe) tersedia agar total 12+ pilihan bahan di form menu
            Ingredient::updateOrCreate(
                ['name' => 'Daging Sapi'],
                [
                    'category' => IngredientCategory::LAUK_HEWANI->value,
                    'unit' => 'kg',
                    'current_stock' => 0,
                    'unit_cost' => 0,
                    'is_active' => true,
                ]
            );
            Ingredient::updateOrCreate(
                ['name' => 'Tempe'],
                [
                    'category' => IngredientCategory::LAUK_NABATI->value,
                    'unit' => 'kg',
                    'current_stock' => 0,
                    'unit_cost' => 0,
                    'is_active' => true,
                ]
            );

            $ingredients = Ingredient::query()->pluck('id', 'name');

            // 1. 6 Rencana Menu Harian (sesuai state desain Stitch)
            $menusSpec = [
                [
                    'offset' => 0,
                    'code' => 'MNU-SS1-01',
                    'name' => 'Nasi, ayam goreng, tumis buncis, tahu, susu',
                    'categories' => ['Siswa', 'Ibu hamil', 'Ibu menyusui', 'Balita'],
                    'target' => 1414,
                    'status' => 'disetujui',
                    'pagu_status' => 'sesuai_pagu',
                    'approval_notes' => 'Kepala Dapur: Siap produksi pagi',
                    'approved_by' => $hendra->id,
                    'approved_at' => $now->copy()->subHours(3),
                    'components' => [
                        'pokok' => 'Beras',
                        'hewani' => 'Ayam Fillet',
                        'nabati' => 'Tahu Putih',
                        'sayur' => 'Buncis',
                        'minuman' => 'Susu UHT',
                    ],
                    'history' => [
                        ['title' => 'Disetujui · Kepala Dapur (Hendra Wijaya)', 'note' => 'Siap produksi pagi', 'at' => $now->copy()->subHours(3)->format('d M Y · H:i').' WIB', 'tone' => 'success'],
                        ['title' => 'Lolos PAGU · Akuntan (Rizky Pratama)', 'note' => 'Sesuai batas PAGU.', 'at' => $now->copy()->subHours(5)->format('d M Y · H:i').' WIB', 'tone' => 'neutral'],
                        ['title' => 'Diajukan · Ahli Gizi (Nadia Putri)', 'note' => null, 'at' => $now->copy()->subHours(7)->format('d M Y · H:i').' WIB', 'tone' => 'neutral'],
                    ],
                ],
                [
                    'offset' => 1,
                    'code' => 'MNU-SS1-02',
                    'name' => 'Nasi, ikan patin bakar, sayur bayam, tempe, susu',
                    'categories' => ['Siswa', 'Ibu hamil', 'Ibu menyusui', 'Balita'],
                    'target' => 1414,
                    'status' => 'disetujui',
                    'pagu_status' => 'sesuai_pagu',
                    'approval_notes' => 'Kepala Dapur: Siap produksi 27 Nov · 26 Nov 2026 07:50 WIB',
                    'approved_by' => $hendra->id,
                    'approved_at' => $now->copy()->subHours(2),
                    'components' => [
                        'pokok' => 'Beras',
                        'hewani' => 'Ikan Patin',
                        'nabati' => 'Tempe',
                        'sayur' => 'Bayam',
                        'minuman' => 'Susu UHT',
                    ],
                    'history' => [
                        ['title' => 'Disetujui · Kepala Dapur (Hendra Wijaya)', 'note' => 'Siap produksi 27 Nov', 'at' => '26 Nov 2026 · 07:50 WIB', 'tone' => 'success'],
                        ['title' => 'Lolos PAGU · Akuntan (Rizky Pratama)', 'note' => 'Sesuai batas PAGU.', 'at' => '26 Nov 2026 · 07:10 WIB', 'tone' => 'neutral'],
                        ['title' => 'Diajukan · Ahli Gizi (Nadia Putri)', 'note' => null, 'at' => '25 Nov 2026 · 16:00 WIB', 'tone' => 'neutral'],
                    ],
                ],
                [
                    'offset' => 2,
                    'code' => 'MNU-SS1-03',
                    'name' => 'Nasi, telur balado, sayur wortel buncis, tahu',
                    'categories' => ['Siswa', 'Balita'],
                    'target' => 1354,
                    'status' => 'menunggu_pemeriksaan_anggaran',
                    'pagu_status' => 'pending',
                    'approval_notes' => 'Ahli Gizi: Diajukan ke Akuntan',
                    'approved_by' => null,
                    'approved_at' => null,
                    'components' => [
                        'pokok' => 'Beras',
                        'hewani' => 'Telur Ayam',
                        'nabati' => 'Tahu Putih',
                        'sayur' => 'Wortel & Buncis',
                        'minuman' => 'Tanpa minuman',
                    ],
                    'history' => [
                        ['title' => 'Diajukan · Ahli Gizi (Nadia Putri)', 'note' => 'Menunggu pemeriksaan PAGU Akuntan.', 'at' => $now->copy()->subHour()->format('d M Y · H:i').' WIB', 'tone' => 'neutral'],
                    ],
                ],
                [
                    'offset' => 3,
                    'code' => 'MNU-SS1-04',
                    'name' => 'Nasi, ayam kecap, capcay wortel, tahu goreng',
                    'categories' => ['Siswa', 'Ibu hamil', 'Ibu menyusui', 'Balita'],
                    'target' => 1414,
                    'status' => 'minta_revisi',
                    'pagu_status' => 'sesuai_pagu',
                    'approval_notes' => 'Kepala Dapur: Ganti lauk hewani, stok ayam terbatas',
                    'approved_by' => null,
                    'approved_at' => null,
                    'components' => [
                        'pokok' => 'Beras',
                        'hewani' => 'Ayam Fillet',
                        'nabati' => 'Tahu Putih',
                        'sayur' => 'Wortel & Buncis',
                        'minuman' => 'Susu UHT',
                    ],
                    'history' => [
                        ['title' => 'Minta Revisi · Kepala Dapur (Hendra Wijaya)', 'note' => 'Ganti lauk hewani, stok ayam terbatas', 'at' => $now->copy()->subDay()->setTime(14, 20)->format('d M Y · H:i').' WIB', 'tone' => 'alert'],
                        ['title' => 'Lolos PAGU · Akuntan (Rizky Pratama)', 'note' => 'Sesuai batas PAGU.', 'at' => $now->copy()->subDay()->setTime(11, 5)->format('d M Y · H:i').' WIB', 'tone' => 'neutral'],
                        ['title' => 'Diajukan · Ahli Gizi (Nadia Putri)', 'note' => null, 'at' => $now->copy()->subDay()->setTime(9, 30)->format('d M Y · H:i').' WIB', 'tone' => 'neutral'],
                    ],
                ],
                [
                    'offset' => 4,
                    'code' => 'MNU-SS1-05',
                    'name' => 'Nasi, rendang daging, tumis bayam, tempe, susu',
                    'categories' => ['Siswa'],
                    'target' => 1284,
                    'status' => 'perlu_revisi_anggaran',
                    'pagu_status' => 'melebihi_pagu',
                    'approval_notes' => 'Akuntan: Melebihi batas PAGU kategori Siswa',
                    'approved_by' => null,
                    'approved_at' => null,
                    'components' => [
                        'pokok' => 'Beras',
                        'hewani' => 'Daging Sapi',
                        'nabati' => 'Tempe',
                        'sayur' => 'Bayam',
                        'minuman' => 'Susu UHT',
                    ],
                    'history' => [
                        ['title' => 'Perlu Revisi Anggaran · Akuntan (Rizky Pratama)', 'note' => 'Melebihi batas PAGU kategori Siswa', 'at' => $now->copy()->subHours(4)->format('d M Y · H:i').' WIB', 'tone' => 'danger'],
                        ['title' => 'Diajukan · Ahli Gizi (Nadia Putri)', 'note' => null, 'at' => $now->copy()->subHours(6)->format('d M Y · H:i').' WIB', 'tone' => 'neutral'],
                    ],
                ],
                [
                    'offset' => 5,
                    'code' => 'MNU-SS1-06',
                    'name' => 'Nasi, ikan patin goreng, tumis buncis, tahu',
                    'categories' => ['Siswa', 'Ibu hamil', 'Balita'],
                    'target' => 1384,
                    'status' => 'draf',
                    'pagu_status' => 'pending',
                    'approval_notes' => 'Ahli Gizi: Belum diajukan',
                    'approved_by' => null,
                    'approved_at' => null,
                    'components' => [
                        'pokok' => 'Beras',
                        'hewani' => 'Ikan Patin',
                        'nabati' => 'Tahu Putih',
                        'sayur' => 'Buncis',
                        'minuman' => 'Tanpa minuman',
                    ],
                    'history' => [
                        ['title' => 'Draf Dibuat · Ahli Gizi (Nadia Putri)', 'note' => 'Belum diajukan ke Akuntan.', 'at' => $now->copy()->subHours(2)->format('d M Y · H:i').' WIB', 'tone' => 'neutral'],
                    ],
                ],
            ];

            $todayMenu = null;
            foreach ($menusSpec as $spec) {
                $menu = DailyMenu::updateOrCreate(
                    ['menu_code' => $spec['code']],
                    [
                        'operational_date' => $now->copy()->addDays($spec['offset'])->toDateString(),
                        'menu_name' => $spec['name'],
                        'target_portions' => $spec['target'],
                        'recipient_category' => 'siswa',
                        'categories_json' => $spec['categories'],
                        'components_json' => $spec['components'],
                        'calories_kcal' => 0,
                        'protein_gram' => 0,
                        'carbs_gram' => 0,
                        'fat_gram' => 0,
                        'fiber_gram' => 0,
                        'estimated_cost_per_portion' => 0,
                        'pagu_status' => $spec['pagu_status'],
                        'status' => $spec['status'],
                        'created_by' => $nadia->id,
                        'approved_by' => $spec['approved_by'],
                        'approved_at' => $spec['approved_at'],
                        'approval_notes' => $spec['approval_notes'],
                        'status_history_json' => $spec['history'],
                    ]
                );

                if ($spec['offset'] === 0) {
                    $todayMenu = $menu;
                }
            }

            if ($todayMenu) {
                // 2. Kebutuhan & Realisasi Bahan Hari Ini (6 bahan)
                $itemsSpec = [
                    ['Beras', 140, 140, 139, 'CV Pangan Nusantara', 'sesuai', null],
                    ['Ayam Fillet', 50, 50, 50, 'UD Segar Kalidoni', 'sesuai', null],
                    ['Buncis', 30, 30, 30, 'Tani Makmur Sumsel', 'menunggu_verifikasi', null],
                    ['Tahu Putih', 55, 53, 53, 'Pemasok belum ditautkan', 'tidak_sesuai', 'Tahu putih kurang 2 kg dari rencana'],
                    ['Susu UHT', 280, 280, 280, 'Pemasok belum ditautkan', 'menunggu_verifikasi', null],
                    ['Minyak Goreng', 10, 10, 10, 'CV Pangan Nusantara', 'sesuai', null],
                ];

                foreach ($itemsSpec as $idx => [$ingName, $planned, $dispatched, $used, $supName, $verifStatus, $verifNotes]) {
                    $ingId = $ingredients[$ingName] ?? null;
                    if (! $ingId) {
                        continue;
                    }

                    DailyMenuItem::updateOrCreate(
                        ['daily_menu_id' => $todayMenu->id, 'ingredient_id' => $ingId],
                        [
                            'qty_per_portion' => round($planned / 1414, 4),
                            'total_planned_qty' => $planned,
                            'dispatched_qty' => $dispatched,
                            'actual_used_qty' => $used,
                        ]
                    );

                    InventoryTransaction::updateOrCreate(
                        ['reference_no' => sprintf('INB-SS1-%s-%02d', $now->format('Ymd'), $idx + 1)],
                        [
                            'ingredient_id' => $ingId,
                            'daily_menu_id' => $todayMenu->id,
                            'type' => 'inbound_purchase',
                            'planned_qty' => $planned,
                            'quantity' => $dispatched,
                            'unit_price' => 0,
                            'total_cost' => 0,
                            'supplier_name' => $supName,
                            'freshness_condition' => $verifStatus === 'tidak_sesuai' ? 'layak' : 'sangat_segar',
                            'verification_status' => $verifStatus,
                            'recorded_by' => $bima->id,
                            'verified_by' => $verifStatus !== 'menunggu_verifikasi' ? $nadia->id : null,
                            'verified_at' => $verifStatus !== 'menunggu_verifikasi' ? $now->copy()->subHours(2) : null,
                            'verification_notes' => $verifNotes,
                        ]
                    );
                }

                // 3. Realisasi Ompreng Terisi (1.414 / 1.414)
                ProductionRun::updateOrCreate(
                    ['daily_menu_id' => $todayMenu->id],
                    [
                        'planned_portions' => 1414,
                        'actual_good_portions' => 1414,
                        'rejected_damaged_portions' => 0,
                        'status' => 'ready_for_dispatch',
                        'supervised_by' => $hendra->id,
                        'volunteers_count' => 35,
                    ]
                );
            }

            // 4. Kendala Bahan & Pemorsian (3 catatan)
            if (ProductionObstacle::count() === 0) {
                foreach ([
                    ['Pasokan ayam fillet datang terlambat 40 menit', 'ditindaklanjuti', $hendra->id],
                    ['Tahu putih kurang 2 kg dari rencana penerimaan', 'terbuka', null],
                    ['Wadah ompreng kurang 20 unit saat pemorsian dini hari', 'ditindaklanjuti', $hendra->id],
                ] as [$desc, $status, $handlerId]) {
                    ProductionObstacle::create([
                        'operational_date' => $today,
                        'daily_menu_id' => $todayMenu?->id,
                        'description' => $desc,
                        'status' => $status,
                        'reported_by' => $nadia->id,
                        'handled_by' => $handlerId,
                    ]);
                }
            }

            // 5. Keamanan Pangan & APD (hari ini Shift Pagi + 6 hari riwayat sebelumnya)
            $defaultTemps = [
                ['label' => 'Bahan baku dingin / kulkas', 'standard' => '0 – 4 °C', 'actual' => 3.2, 'compliant' => true],
                ['label' => 'Bahan baku beku / freezer', 'standard' => '≤ -18 °C', 'actual' => -19.0, 'compliant' => true],
                ['label' => 'Penyimpanan bahan kering', 'standard' => '18 – 25 °C', 'actual' => 22.5, 'compliant' => true],
                ['label' => 'Makanan matang / inti masakan', 'standard' => '≥ 75 °C', 'actual' => 82.0, 'compliant' => true],
                ['label' => 'Makanan siap saji di wadah distribusi', 'standard' => '≥ 60 °C', 'actual' => 56.5, 'compliant' => false],
                ['label' => 'Ruang pengolahan / area pemorsian', 'standard' => '≤ 25 °C', 'actual' => 23.8, 'compliant' => true],
            ];
            $defaultApd = [
                ['label' => 'Penutup kepala / hairnet terpasang penuh', 'compliant' => true],
                ['label' => 'Masker menutup hidung dan mulut', 'compliant' => true],
                ['label' => 'Celemek / apron bersih dan utuh', 'compliant' => true],
                ['label' => 'Sarung tangan sekali pakai saat pemorsian', 'compliant' => true],
                ['label' => 'Alas kaki tertutup dan anti-selip', 'compliant' => true],
                ['label' => 'Kuku pendek, bersih, dan tanpa perhiasan', 'compliant' => true],
            ];

            FoodSafetyCheck::updateOrCreate(
                ['check_date' => $today, 'shift' => 'Pagi'],
                [
                    'temperatures_json' => $defaultTemps,
                    'hygiene_json' => [],
                    'apd_json' => $defaultApd,
                    'total_volunteers' => 35,
                    'compliant_volunteers' => 35,
                    'notes' => 'Suhu makanan siap saji di wadah distribusi tercatat 56,5 °C, telah dilaporkan ke petugas pemorsian untuk pemanasan ulang.',
                    'inspector_id' => $nadia->id,
                    'checked_at' => $now->copy()->setTime(7, 45),
                ]
            );

            $historyShifts = [
                [1, [['Pagi', 35, ''], ['Siang', 34, '1 temuan penutup rambut'], ['Malam', 35, '']]],
                [2, [['Pagi', 35, ''], ['Siang', 35, 'Seluruh shift lengkap'], ['Malam', 35, '']]],
                [3, [['Pagi', 35, ''], ['Siang', 35, ''], ['Malam', 34, '1 celemek diganti baru']]],
                [4, [['Pagi', 35, ''], ['Siang', 35, 'Seluruh shift lengkap'], ['Malam', 35, '']]],
                [5, [['Pagi', 34, '1 relawan melengkapi sarung tangan'], ['Siang', 35, ''], ['Malam', 35, '']]],
                [6, [['Pagi', 35, ''], ['Siang', 35, 'Seluruh shift lengkap'], ['Malam', 35, '']]],
            ];

            foreach ($historyShifts as [$daysAgo, $shifts]) {
                $d = $now->copy()->subDays($daysAgo)->toDateString();
                foreach ($shifts as [$sh, $comp, $note]) {
                    FoodSafetyCheck::updateOrCreate(
                        ['check_date' => $d, 'shift' => $sh],
                        [
                            'temperatures_json' => $defaultTemps,
                            'hygiene_json' => [],
                            'apd_json' => $defaultApd,
                            'total_volunteers' => 35,
                            'compliant_volunteers' => $comp,
                            'notes' => $note ?: null,
                            'inspector_id' => $nadia->id,
                            'checked_at' => $now->copy()->subDays($daysAgo),
                        ]
                    );
                }
            }

            if (FoodSafetyFinding::count() === 0) {
                FoodSafetyFinding::create([
                    'finding_date' => $today,
                    'type' => 'Suhu',
                    'description' => 'Suhu makanan siap saji di bawah standar SOP',
                    'status' => 'terbuka',
                    'handled_by' => null,
                    'handler_note' => 'Menunggu Kepala Dapur',
                ]);

                FoodSafetyFinding::create([
                    'finding_date' => $now->copy()->subDay()->toDateString(),
                    'type' => 'APD',
                    'description' => 'Relawan tanpa penutup rambut',
                    'status' => 'ditindaklanjuti',
                    'handled_by' => $hendra->id,
                    'handler_note' => 'Kepala Dapur (Hendra Wijaya)',
                ]);
            }

            // 6. Keluhan aktif (1 entri tanpa kata "kuah")
            $pesantren = Location::where('name', 'Pesantren Al-Falah Palembang')->first();
            $inst = BeneficiaryInstitution::firstOrCreate(
                ['code' => 'PON-01'],
                [
                    'name' => 'Pesantren Al-Falah Palembang',
                    'type' => 'pesantren',
                    'default_quota' => 350,
                    'address' => 'Jl. Mayor Zen No. 45, Sungai Selayur, Kalidoni, Palembang',
                    'is_active' => true,
                ]
            );

            ComplaintTicket::updateOrCreate(
                ['ticket_number' => 'TKT-SS1-001'],
                [
                    'beneficiary_institution_id' => $inst->id,
                    'location_id' => $pesantren?->id,
                    'reported_by' => $ahmad?->id ?? $nadia->id,
                    'category' => 'ompreng_rusak',
                    'severity' => 'sedang',
                    'description' => 'Laporan Guru PIC: tumis buncis tumpah pada 4 ompreng saat pengantaran pagi.',
                    'status' => 'open',
                    'reported_at' => $now->copy()->setTime(8, 45),
                ]
            );
        } finally {
            AuditLogService::$enabled = true;
        }
    }
}
