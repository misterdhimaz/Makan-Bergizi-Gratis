<?php

namespace Database\Seeders;

use App\Enums\DeliveryStatus;
use App\Enums\IngredientCategory;
use App\Enums\SopChecklistType;
use App\Enums\UserRole;
use App\Models\AuditLog;
use App\Models\DataRevision;
use App\Models\DeliveryRoute;
use App\Models\Ingredient;
use App\Models\Location;
use App\Models\LocationCategoryQuota;
use App\Models\PaguParameter;
use App\Models\PortionStandard;
use App\Models\RecipientCategory;
use App\Models\SopChecklist;
use App\Models\Supplier;
use App\Models\SystemBackup;
use App\Models\SystemSetting;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class RekagiziAdminSeeder extends Seeder
{
    public function run(): void
    {
        AuditLogService::$enabled = false;

        try {
            $now = now();
            $today = $now->toDateString();

            // 1. Kategori Penerima (4)
            $categoriesData = [
                ['code' => 'KAT-SISWA', 'name' => 'Siswa', 'description' => 'Peserta didik SD dan Pesantren'],
                ['code' => 'KAT-BUMIL', 'name' => 'Ibu hamil', 'description' => 'Ibu hamil penerima manfaat posyandu'],
                ['code' => 'KAT-BUSUI', 'name' => 'Ibu menyusui', 'description' => 'Ibu menyusui penerima manfaat posyandu'],
                ['code' => 'KAT-BALITA', 'name' => 'Balita', 'description' => 'Anak balita penerima manfaat posyandu'],
            ];

            $categories = [];
            foreach ($categoriesData as $cat) {
                $categories[$cat['name']] = RecipientCategory::updateOrCreate(
                    ['code' => $cat['code']],
                    ['name' => $cat['name'], 'description' => $cat['description'], 'is_active' => true]
                );
            }

            // 2. Lokasi (7 titik, total 1.414 porsi) + Rincian Kuota Kategori
            $locationsData = [
                [
                    'code' => 'POS-01',
                    'name' => 'Posyandu Mawar Kalidoni',
                    'type' => 'posyandu',
                    'address' => 'Jl. Sungai Selayur Lr. Mawar RT 04, Kalidoni, Palembang',
                    'target_portions' => 60,
                    'quotas' => ['Ibu hamil' => 15, 'Ibu menyusui' => 15, 'Balita' => 30],
                ],
                [
                    'code' => 'POS-02',
                    'name' => 'Posyandu Melati Kalidoni',
                    'type' => 'posyandu',
                    'address' => 'Jl. Mayor Zen Lr. Melati RT 09, Kalidoni, Palembang',
                    'target_portions' => 70,
                    'quotas' => ['Ibu hamil' => 15, 'Ibu menyusui' => 15, 'Balita' => 40],
                ],
                [
                    'code' => 'SCH-129',
                    'name' => 'SD Negeri 129 Palembang',
                    'type' => 'sd',
                    'address' => 'Jl. Taqwa Mata Merah, Sungai Selayur, Kalidoni, Palembang',
                    'target_portions' => 260,
                    'quotas' => ['Siswa' => 260],
                ],
                [
                    'code' => 'SCH-130',
                    'name' => 'SD Negeri 130 Palembang',
                    'type' => 'sd',
                    'address' => 'Jl. Sungai Selayur No. 22, Kalidoni, Palembang',
                    'target_portions' => 220,
                    'quotas' => ['Siswa' => 220],
                ],
                [
                    'code' => 'SCH-131',
                    'name' => 'SD Negeri 131 Palembang',
                    'type' => 'sd',
                    'address' => 'Jl. Kalidoni Indah No. 11, Kalidoni, Palembang',
                    'target_portions' => 210,
                    'quotas' => ['Siswa' => 210],
                ],
                [
                    'code' => 'SCH-132',
                    'name' => 'SD Negeri 132 Palembang',
                    'type' => 'sd',
                    'address' => 'Jl. Sekojo Ujung No. 08, Kalidoni, Palembang',
                    'target_portions' => 244,
                    'quotas' => ['Siswa' => 244],
                ],
                [
                    'code' => 'PON-01',
                    'name' => 'Pesantren Al-Falah Palembang',
                    'type' => 'pesantren',
                    'address' => 'Jl. Mayor Zen No. 45, Sungai Selayur, Kalidoni, Palembang',
                    'target_portions' => 350,
                    'quotas' => ['Siswa' => 350],
                ],
            ];

            $locations = [];
            foreach ($locationsData as $locData) {
                $loc = Location::updateOrCreate(
                    ['code' => $locData['code']],
                    [
                        'name' => $locData['name'],
                        'type' => $locData['type'],
                        'address' => $locData['address'],
                        'target_portions' => $locData['target_portions'],
                        'is_active' => true,
                    ]
                );
                $locations[$locData['name']] = $loc;

                foreach ($locData['quotas'] as $catName => $count) {
                    LocationCategoryQuota::updateOrCreate(
                        [
                            'location_id' => $loc->id,
                            'recipient_category_id' => $categories[$catName]->id,
                        ],
                        ['portion_count' => $count]
                    );
                }
            }

            // 3. Akun Pengguna (17 total = 15 aktif + 2 nonaktif, termasuk 2 belum pernah login)
            $randomHash = fn (): string => Hash::make(Str::password(16));

            $usersData = [
                // 1 Administrator Sistem (aktif)
                ['username' => 'arief.mahendra', 'name' => 'Arief Mahendra', 'email' => 'arief.mahendra@mbg-selayur1.id', 'role' => UserRole::ADMIN, 'location' => null, 'is_active' => true, 'last_login_at' => $now->copy()->subHours(2)],
                // 2 Ahli Gizi (aktif)
                ['username' => 'nadia.putri', 'name' => 'Nadia Putri', 'email' => 'nadia.putri@mbg-selayur1.id', 'role' => UserRole::AHLI_GIZI, 'location' => null, 'is_active' => true, 'last_login_at' => $now->copy()->subHours(4)],
                ['username' => 'siti.rahmawati', 'name' => 'Siti Rahmawati', 'email' => 'siti.rahmawati@mbg-selayur1.id', 'role' => UserRole::AHLI_GIZI, 'location' => null, 'is_active' => true, 'last_login_at' => $now->copy()->subDay()],
                // 1 Akuntan (aktif)
                ['username' => 'rizky.pratama', 'name' => 'Rizky Pratama', 'email' => 'rizky.pratama@mbg-selayur1.id', 'role' => UserRole::AKUNTAN, 'location' => null, 'is_active' => true, 'last_login_at' => $now->copy()->subHours(3)],
                // 1 Kepala Dapur (aktif)
                ['username' => 'hendra.wijaya', 'name' => 'Hendra Wijaya', 'email' => 'hendra.wijaya@mbg-selayur1.id', 'role' => UserRole::KEPALA_DAPUR, 'location' => null, 'is_active' => true, 'last_login_at' => $now->copy()->subHours(3)],
                // 1 Asisten Lapangan (aktif)
                ['username' => 'bima.santoso', 'name' => 'Bima Santoso', 'email' => 'bima.santoso@mbg-selayur1.id', 'role' => UserRole::ASISTEN_LAPANGAN, 'location' => null, 'is_active' => true, 'last_login_at' => $now->copy()->subDay()],
                // 2 Tim Distribusi (aktif)
                ['username' => 'fajar.nugroho', 'name' => 'Fajar Nugroho', 'email' => 'fajar.nugroho@mbg-selayur1.id', 'role' => UserRole::TIM_DISTRIBUSI, 'location' => null, 'is_active' => true, 'last_login_at' => $now->copy()->subHours(1)],
                ['username' => 'dedi.kurniawan', 'name' => 'Dedi Kurniawan', 'email' => 'dedi.kurniawan@mbg-selayur1.id', 'role' => UserRole::TIM_DISTRIBUSI, 'location' => null, 'is_active' => true, 'last_login_at' => null],
                // 7 Guru PIC aktif (1 per lokasi)
                ['username' => 'fitria.handayani', 'name' => 'Fitria Handayani', 'email' => 'fitria.handayani@mbg-selayur1.id', 'role' => UserRole::GURU_PIC, 'location' => 'Posyandu Mawar Kalidoni', 'is_active' => true, 'last_login_at' => $now->copy()->subHours(2)],
                ['username' => 'marlina.sari', 'name' => 'Marlina Sari', 'email' => 'marlina.sari@mbg-selayur1.id', 'role' => UserRole::GURU_PIC, 'location' => 'Posyandu Melati Kalidoni', 'is_active' => true, 'last_login_at' => $now->copy()->subHours(5)],
                ['username' => 'suryati.pic', 'name' => 'Suryati', 'email' => 'suryati@mbg-selayur1.id', 'role' => UserRole::GURU_PIC, 'location' => 'SD Negeri 129 Palembang', 'is_active' => true, 'last_login_at' => $now->copy()->subHours(2)],
                ['username' => 'agus.prasetyo', 'name' => 'Agus Prasetyo', 'email' => 'agus.prasetyo@mbg-selayur1.id', 'role' => UserRole::GURU_PIC, 'location' => 'SD Negeri 130 Palembang', 'is_active' => true, 'last_login_at' => $now->copy()->subDays(2)],
                ['username' => 'rina.marlina', 'name' => 'Rina Marlina', 'email' => 'rina.marlina@mbg-selayur1.id', 'role' => UserRole::GURU_PIC, 'location' => 'SD Negeri 131 Palembang', 'is_active' => true, 'last_login_at' => $now->copy()->subDays(1)],
                ['username' => 'dewi.sartika', 'name' => 'Dewi Sartika', 'email' => 'dewi.sartika@mbg-selayur1.id', 'role' => UserRole::GURU_PIC, 'location' => 'SD Negeri 132 Palembang', 'is_active' => true, 'last_login_at' => null],
                ['username' => 'ahmad.fauzi', 'name' => 'Ahmad Fauzi', 'email' => 'ahmad.fauzi@mbg-selayur1.id', 'role' => UserRole::GURU_PIC, 'location' => 'Pesantren Al-Falah Palembang', 'is_active' => true, 'last_login_at' => $now->copy()->subHours(6)],
                // 2 Akun Nonaktif
                ['username' => 'rina.safitri', 'name' => 'Rina Safitri', 'email' => 'rina.safitri@mbg-selayur1.id', 'role' => UserRole::GURU_PIC, 'location' => 'SD Negeri 130 Palembang', 'is_active' => false, 'last_login_at' => $now->copy()->subDays(7)],
                ['username' => 'bagus.setiawan', 'name' => 'Bagus Setiawan', 'email' => 'bagus.setiawan@mbg-selayur1.id', 'role' => UserRole::TIM_DISTRIBUSI, 'location' => null, 'is_active' => false, 'last_login_at' => $now->copy()->subDays(14)],
            ];

            $seededUsers = [];
            foreach ($usersData as $u) {
                $seededUsers[$u['username']] = User::updateOrCreate(
                    ['username' => $u['username']],
                    [
                        'name' => $u['name'],
                        'email' => $u['email'],
                        'password' => $randomHash(),
                        'role' => $u['role'],
                        'location_id' => $u['location'] ? $locations[$u['location']]->id : null,
                        'is_active' => $u['is_active'],
                        'must_change_password' => true,
                        'password_expires_at' => $now->copy()->addHours(24),
                        'failed_login_attempts' => 0,
                        'locked_until' => null,
                        'last_login_at' => $u['last_login_at'],
                    ]
                );
            }

            // 4. Rute Pengiriman Hari Contoh (Relatif terhadap now() -> menghasilkan 1.170 / 318 / 850 / -2 / 4)
            $courier = $seededUsers['fajar.nugroho'];
            $routesData = [
                ['waybill' => 'SJ/SS1/001', 'location' => 'Posyandu Mawar Kalidoni', 'status' => DeliveryStatus::DITERIMA, 'dispatched' => 60, 'confirmed' => 60, 'confirmer' => 'fitria.handayani'],
                ['waybill' => 'SJ/SS1/002', 'location' => 'Posyandu Melati Kalidoni', 'status' => DeliveryStatus::TIBA, 'dispatched' => 70, 'confirmed' => null, 'confirmer' => null],
                ['waybill' => 'SJ/SS1/003', 'location' => 'SD Negeri 129 Palembang', 'status' => DeliveryStatus::DITERIMA, 'dispatched' => 260, 'confirmed' => 258, 'confirmer' => 'suryati.pic'],
                ['waybill' => 'SJ/SS1/004', 'location' => 'SD Negeri 130 Palembang', 'status' => DeliveryStatus::BERANGKAT, 'dispatched' => 220, 'confirmed' => null, 'confirmer' => null],
                ['waybill' => 'SJ/SS1/005', 'location' => 'SD Negeri 131 Palembang', 'status' => DeliveryStatus::BERANGKAT, 'dispatched' => 210, 'confirmed' => null, 'confirmer' => null],
                ['waybill' => 'SJ/SS1/006', 'location' => 'SD Negeri 132 Palembang', 'status' => DeliveryStatus::DISIAPKAN, 'dispatched' => 244, 'confirmed' => null, 'confirmer' => null],
                ['waybill' => 'SJ/SS1/007', 'location' => 'Pesantren Al-Falah Palembang', 'status' => DeliveryStatus::BERMASALAH, 'dispatched' => 350, 'confirmed' => null, 'confirmer' => null],
            ];

            foreach ($routesData as $r) {
                DeliveryRoute::updateOrCreate(
                    ['waybill_number' => $r['waybill']],
                    [
                        'operational_date' => $today,
                        'location_id' => $locations[$r['location']]->id,
                        'status' => $r['status'],
                        'dispatched_portions' => $r['dispatched'],
                        'confirmed_portions' => $r['confirmed'],
                        'courier_id' => $courier->id,
                        'confirmed_by' => $r['confirmer'] ? $seededUsers[$r['confirmer']]->id : null,
                        'departed_at' => $r['status'] !== DeliveryStatus::DISIAPKAN ? $now->copy()->subHours(2) : null,
                        'arrived_at' => in_array($r['status'], [DeliveryStatus::TIBA, DeliveryStatus::DITERIMA, DeliveryStatus::BERMASALAH], true) ? $now->copy()->subHour() : null,
                        'confirmed_at' => $r['status'] === DeliveryStatus::DITERIMA ? $now->copy()->subMinutes(30) : null,
                    ]
                );
            }

            // 5. Pemasok (5) — 2 belum memiliki kontak agar Data Master Pemasok berstatus belum lengkap (5 dari 7)
            $suppliersData = [
                ['code' => 'SUP-01', 'name' => 'CV Pangan Nusantara', 'contact_person' => 'Hendra Kusuma', 'phone' => '0812-7100-1101', 'category_focus' => 'Pokok'],
                ['code' => 'SUP-02', 'name' => 'UD Segar Kalidoni', 'contact_person' => 'Sri Wahyuni', 'phone' => '0813-7100-2202', 'category_focus' => 'Lauk hewani'],
                ['code' => 'SUP-03', 'name' => 'Tani Makmur Sumsel', 'contact_person' => 'Joko Susilo', 'phone' => '0821-7100-3303', 'category_focus' => 'Sayur'],
                ['code' => 'SUP-04', 'name' => 'Koperasi Nelayan Musi', 'contact_person' => null, 'phone' => null, 'category_focus' => 'Lauk hewani'],
                ['code' => 'SUP-05', 'name' => 'Toko Bumbu Selayur', 'contact_person' => null, 'phone' => null, 'category_focus' => 'Pokok'],
            ];

            $suppliers = [];
            foreach ($suppliersData as $sup) {
                $suppliers[$sup['name']] = Supplier::updateOrCreate(
                    ['code' => $sup['code']],
                    [
                        'name' => $sup['name'],
                        'contact_person' => $sup['contact_person'],
                        'phone' => $sup['phone'],
                        'address' => 'Kec. Kalidoni, Kota Palembang',
                        'category_focus' => $sup['category_focus'],
                        'is_active' => true,
                    ]
                );
            }

            // 6. Daftar Bahan (11, tanpa sku & tanpa min_stock)
            $ingredientsData = [
                ['name' => 'Beras', 'category' => IngredientCategory::POKOK->value, 'unit' => 'kg', 'supplier' => 'CV Pangan Nusantara'],
                ['name' => 'Minyak Goreng', 'category' => IngredientCategory::POKOK->value, 'unit' => 'liter', 'supplier' => 'CV Pangan Nusantara'],
                ['name' => 'Gula', 'category' => IngredientCategory::POKOK->value, 'unit' => 'kg', 'supplier' => 'CV Pangan Nusantara'],
                ['name' => 'Ayam Fillet', 'category' => IngredientCategory::LAUK_HEWANI->value, 'unit' => 'kg', 'supplier' => 'UD Segar Kalidoni'],
                ['name' => 'Telur Ayam', 'category' => IngredientCategory::LAUK_HEWANI->value, 'unit' => 'kg', 'supplier' => 'UD Segar Kalidoni'],
                ['name' => 'Wortel', 'category' => IngredientCategory::SAYUR->value, 'unit' => 'kg', 'supplier' => 'Tani Makmur Sumsel'],
                ['name' => 'Buncis', 'category' => IngredientCategory::SAYUR->value, 'unit' => 'kg', 'supplier' => 'Tani Makmur Sumsel'],
                ['name' => 'Bayam', 'category' => IngredientCategory::SAYUR->value, 'unit' => 'ikat', 'supplier' => 'Tani Makmur Sumsel'],
                ['name' => 'Ikan Patin', 'category' => IngredientCategory::LAUK_HEWANI->value, 'unit' => 'kg', 'supplier' => null],
                ['name' => 'Tahu Putih', 'category' => IngredientCategory::LAUK_NABATI->value, 'unit' => 'potong', 'supplier' => null],
                ['name' => 'Susu UHT', 'category' => IngredientCategory::MINUMAN->value, 'unit' => 'kotak', 'supplier' => null],
            ];

            foreach ($ingredientsData as $ing) {
                Ingredient::updateOrCreate(
                    ['name' => $ing['name']],
                    [
                        'category' => $ing['category'],
                        'unit' => $ing['unit'],
                        'current_stock' => 0,
                        'unit_cost' => 0,
                        'supplier_id' => $ing['supplier'] ? $suppliers[$ing['supplier']]->id : null,
                        'is_active' => true,
                    ]
                );
            }

            // 7. Standar Porsi (18 baris, memakai quantity + unit & meal_component enum IngredientCategory)
            $portionsData = [
                'Siswa' => [
                    [IngredientCategory::POKOK, 100, 'g'],
                    [IngredientCategory::LAUK_HEWANI, 60, 'g'],
                    [IngredientCategory::LAUK_NABATI, 40, 'g'],
                    [IngredientCategory::SAYUR, 75, 'g'],
                ],
                'Ibu hamil' => [
                    [IngredientCategory::POKOK, 120, 'g'],
                    [IngredientCategory::LAUK_HEWANI, 80, 'g'],
                    [IngredientCategory::LAUK_NABATI, 50, 'g'],
                    [IngredientCategory::SAYUR, 100, 'g'],
                    [IngredientCategory::MINUMAN, 200, 'ml'],
                ],
                'Ibu menyusui' => [
                    [IngredientCategory::POKOK, 120, 'g'],
                    [IngredientCategory::LAUK_HEWANI, 80, 'g'],
                    [IngredientCategory::LAUK_NABATI, 50, 'g'],
                    [IngredientCategory::SAYUR, 100, 'g'],
                    [IngredientCategory::MINUMAN, 200, 'ml'],
                ],
                'Balita' => [
                    [IngredientCategory::POKOK, 60, 'g'],
                    [IngredientCategory::LAUK_HEWANI, 45, 'g'],
                    [IngredientCategory::LAUK_NABATI, 30, 'g'],
                    [IngredientCategory::SAYUR, 50, 'g'],
                ],
            ];

            foreach ($portionsData as $catName => $items) {
                foreach ($items as [$comp, $qty, $unit]) {
                    PortionStandard::updateOrCreate(
                        [
                            'recipient_category_id' => $categories[$catName]->id,
                            'meal_component' => $comp->value,
                        ],
                        [
                            'quantity' => $qty,
                            'unit' => $unit,
                            'notes' => 'Data contoh standar porsi BGN',
                            'is_active' => true,
                        ]
                    );
                }
            }

            // 8. Parameter PAGU (Rp 10.000 / 15.000 / 15.000 / 8.000, berlaku 1 Nov 2026)
            $paguData = [
                'Siswa' => 10000,
                'Ibu hamil' => 15000,
                'Ibu menyusui' => 15000,
                'Balita' => 8000,
            ];
            foreach ($paguData as $catName => $budget) {
                PaguParameter::updateOrCreate(
                    ['recipient_category_id' => $categories[$catName]->id],
                    [
                        'max_budget_per_portion' => $budget,
                        'effective_date' => '2026-11-01',
                        'notes' => 'Data contoh batas PAGU BGN',
                        'is_active' => true,
                    ]
                );
            }

            // 9. Checklist SOP BGN (12 item: 6 suhu + 6 apd + 0 kebersihan)
            $sopItems = [
                ['SOP-SH-01', SopChecklistType::SUHU, 'Suhu inti masakan matang (≥ 75°C)', '≥ 75°C', 'Setiap batch'],
                ['SOP-SH-02', SopChecklistType::SUHU, 'Suhu penyimpanan bahan dingin (≤ 4°C)', '≤ 4°C', '2x sehari'],
                ['SOP-SH-03', SopChecklistType::SUHU, 'Suhu makanan siap antar di ompreng (≥ 60°C)', '≥ 60°C', 'Sebelum muat'],
                ['SOP-SH-04', SopChecklistType::SUHU, 'Suhu air bilas sanitasi peralatan (≥ 70°C)', '≥ 70°C', 'Setiap shift'],
                ['SOP-SH-05', SopChecklistType::SUHU, 'Suhu freezer penyimpanan protein hewani (≤ -18°C)', '≤ -18°C', '2x sehari'],
                ['SOP-SH-06', SopChecklistType::SUHU, 'Suhu makanan saat tiba di titik penerima (≥ 55°C)', '≥ 55°C', 'Saat serah terima'],
                ['SOP-AP-01', SopChecklistType::APD, 'Penutup kepala & masker kerja terpakai', '100% relawan', 'Setiap shift'],
                ['SOP-AP-02', SopChecklistType::APD, 'Sarung tangan food-grade saat pemorsian', '100% relawan', 'Setiap batch'],
                ['SOP-AP-03', SopChecklistType::APD, 'Celemek tahan air bersih dan terpasang rapi', '100% relawan', 'Setiap shift'],
                ['SOP-AP-04', SopChecklistType::APD, 'Alas kaki tertutup anti-selip di area dapur', '100% relawan', 'Setiap shift'],
                ['SOP-AP-05', SopChecklistType::APD, 'Kuku pendek bersih tanpa cat kuku dan perhiasan', '100% relawan', 'Setiap shift'],
                ['SOP-AP-06', SopChecklistType::APD, 'Cuci tangan pakai sabun sebelum menyentuh pangan', '100% relawan', 'Setiap proses'],
            ];

            foreach ($sopItems as [$code, $type, $title, $threshold, $freq]) {
                SopChecklist::updateOrCreate(
                    ['code' => $code],
                    [
                        'type' => $type,
                        'title' => $title,
                        'threshold_value' => $threshold,
                        'frequency' => $freq,
                        'description' => 'Data contoh item Keamanan Pangan & APD',
                        'is_active' => true,
                    ]
                );
            }

            // 10. System Settings
            $settings = [
                'session_idle_timeout_minutes' => ['30', 'security', 'Batas waktu sesi tidak aktif (menit)'],
                'max_failed_login_attempts' => ['5', 'security', 'Batas percobaan gagal login sebelum akun dikunci'],
                'account_lock_duration_minutes' => ['15', 'security', 'Durasi penguncian akun otomatis (menit)'],
                'password_min_length' => ['8', 'password', 'Panjang minimal kata sandi'],
                'password_require_mixed_case_number' => ['1', 'password', 'Wajib huruf besar, huruf kecil, dan angka'],
                'force_password_change_first_login' => ['1', 'password', 'Wajib ganti kata sandi sementara saat login pertama'],
                'logout_on_role_change' => ['1', 'security', 'Akhiri sesi aktif otomatis saat peran akun diubah'],
            ];

            foreach ($settings as $key => [$val, $group, $desc]) {
                SystemSetting::updateOrCreate(
                    ['key' => $key],
                    ['value' => $val, 'group' => $group, 'description' => $desc, 'updated_by' => $seededUsers['arief.mahendra']->id]
                );
            }

            // 11. System Backups (data contoh dengan berkas SQL nyata di disk privat)
            if (SystemBackup::count() === 0) {
                $admin = $seededUsers['arief.mahendra'];
                Storage::disk('local')->makeDirectory('backups');

                foreach ([
                    ['backup_ss1_harian_01.sql', 'scheduled', $now->copy()->startOfDay()->addHours(2)],
                    ['backup_ss1_harian_02.sql', 'scheduled', $now->copy()->subDay()->startOfDay()->addHours(2)],
                    ['backup_ss1_harian_03.sql', 'scheduled', $now->copy()->subDays(2)->startOfDay()->addHours(2)],
                    ['backup_ss1_manual_01.sql', 'manual', $now->copy()->subDays(3)->setTime(15, 10)],
                ] as [$fname, $type, $ts]) {
                    $sampleSql = "-- REKAGIZI MBG SPPG Sungai Selayur 1 ({$type})\n-- Generated at {$ts->toIso8601String()}\nSELECT 1;\n";
                    $relativePath = 'backups/'.$fname;
                    Storage::disk('local')->put($relativePath, $sampleSql);

                    SystemBackup::create([
                        'filename' => $fname,
                        'disk' => 'local',
                        'path' => $relativePath,
                        'size_bytes' => strlen($sampleSql),
                        'trigger_type' => $type,
                        'status' => 'success',
                        'created_by' => $type === 'scheduled' ? null : $admin->id,
                        'created_at' => $ts,
                        'updated_at' => $ts,
                    ]);
                }
            }

            // 12. Audit Logs (187 entri contoh, tanggal relatif terhadap now(), termasuk 3 login_gagal 24 jam + 1 revisi data bulan ini)
            if (AuditLog::count() === 0) {
                $admin = $seededUsers['arief.mahendra'];
                $akuntan = $seededUsers['rizky.pratama'];
                $logsBatch = [];

                // 3 login gagal dalam 24 jam terakhir
                foreach ([2, 6, 14] as $hoursAgo) {
                    $ts = $now->copy()->subHours($hoursAgo);
                    $logsBatch[] = [
                        'occurred_at' => $ts,
                        'user_id' => null,
                        'user_name_snapshot' => 'Percobaan Login (rina.safitri)',
                        'role' => 'Guru PIC / Sekolah & Posyandu',
                        'action' => 'login_gagal',
                        'target' => 'rina.safitri',
                        'module' => 'Autentikasi',
                        'ip_address' => '192.168.1.45',
                        'old_values' => null,
                        'new_values' => json_encode(['reason' => 'Akun nonaktif atau kata sandi salah']),
                        'reason' => 'Percobaan masuk gagal (data contoh)',
                        'approver_id' => null,
                        'created_at' => $ts,
                    ];
                }

                // 1 revisi data terkunci bulan ini
                $revTs = $now->copy()->subDays(2);
                $logsBatch[] = [
                    'occurred_at' => $revTs,
                    'user_id' => $akuntan->id,
                    'user_name_snapshot' => $akuntan->name,
                    'role' => 'Akuntan',
                    'action' => 'revisi_data',
                    'target' => 'Parameter PAGU · Siswa',
                    'module' => 'Data Master · Parameter PAGU',
                    'ip_address' => '127.0.0.1',
                    'old_values' => json_encode(['max_budget_per_portion' => 9500]),
                    'new_values' => json_encode(['max_budget_per_portion' => 10000]),
                    'reason' => 'Penyesuaian indeks harga bahan pokok Palembang',
                    'approver_id' => $admin->id,
                    'created_at' => $revTs,
                ];

                DataRevision::create([
                    'revisable_type' => PaguParameter::class,
                    'revisable_id' => PaguParameter::first()->id,
                    'old_values' => ['max_budget_per_portion' => 9500],
                    'new_values' => ['max_budget_per_portion' => 10000],
                    'reason' => 'Penyesuaian indeks harga bahan pokok Palembang',
                    'requested_by' => $akuntan->id,
                    'approved_by' => $admin->id,
                    'approved_at' => $revTs,
                ]);

                // 183 entri operasional/admin lainnya -> total 187 entri
                $sampleActors = array_values(array_filter($seededUsers, fn (User $u) => $u->is_active));
                $sampleActions = [
                    ['login', 'Autentikasi', 'Sesi Masuk Portal'],
                    ['ubah', 'Data Master · Lokasi Penerima', 'SD Negeri 129 Palembang'],
                    ['tambah', 'Data Master · Daftar Bahan', 'Ayam Fillet'],
                    ['ubah', 'Pengaturan Sistem', 'Keamanan Sesi'],
                ];

                for ($i = 0; $i < 183; $i++) {
                    $actor = $sampleActors[$i % count($sampleActors)];
                    [$act, $mod, $tgt] = $sampleActions[$i % count($sampleActions)];
                    $ts = $now->copy()->subMinutes(($i + 1) * 90);
                    $logsBatch[] = [
                        'occurred_at' => $ts,
                        'user_id' => $actor->id,
                        'user_name_snapshot' => $actor->name,
                        'role' => $actor->role->label(),
                        'action' => $act,
                        'target' => $tgt,
                        'module' => $mod,
                        'ip_address' => '127.0.0.1',
                        'old_values' => null,
                        'new_values' => null,
                        'reason' => 'Data contoh log aktivitas',
                        'approver_id' => null,
                        'created_at' => $ts,
                    ];
                }

                foreach (array_chunk($logsBatch, 50) as $chunk) {
                    DB::table('audit_logs')->insert($chunk);
                }
            }
        } finally {
            AuditLogService::$enabled = true;
        }
    }
}
