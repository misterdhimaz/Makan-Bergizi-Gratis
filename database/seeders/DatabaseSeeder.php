<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\ActivityLog;
use App\Models\ApdInspection;
use App\Models\BeneficiaryInstitution;
use App\Models\ComplaintTicket;
use App\Models\DailyMenu;
use App\Models\DailyMenuItem;
use App\Models\Delivery;
use App\Models\DeliveryConfirmation;
use App\Models\Ingredient;
use App\Models\InventoryTransaction;
use App\Models\ProductionRun;
use App\Models\QcInspection;
use App\Models\SopConfiguration;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seeder Resmi SPPG Sungai Selayur 1, Kalidoni, Kota Palembang
     * Sesuai Dokumen Project Scope Statement Tim REKAGIZI:
     * - Tepat 1.414 Penerima Manfaat (5 Sekolah [1 Pesantren, 4 SD] + 2 Posyandu)
     * - 35 Relawan Dapur
     * - 7 Peran Pengguna RBAC
     * - 8 Fitur Fungsional & SOP BGN Terintegrasi
     */
    public function run(): void
    {
        $password = Hash::make('password123');

        // 1. DATA MASTER MITRA: 5 Sekolah (1 Pesantren, 4 SD) + 2 Posyandu (Total Kuota = TEPAT 1.414 PORSI)
        $institutionsData = [
            [
                'code' => 'SCH-SS1-001',
                'name' => 'Pondok Pesantren Muqimus Sunnah Kalidoni',
                'type' => 'pesantren',
                'recipient_category' => 'siswa',
                'default_quota' => 350,
                'address' => 'Jl. Mayor Zen No. 45, RT 12 RW 04, Kel. Sungai Selayur, Kec. Kalidoni, Kota Palembang',
                'latitude' => -2.9712000,
                'longitude' => 104.7954000,
                'contact_phone' => '0813-7312-4501',
                'pic_name' => 'Ust. Ahmad Fauzi, S.Pd.I',
                'pic_phone' => '0813-7312-4501',
                'is_active' => true,
            ],
            [
                'code' => 'SCH-SS1-002',
                'name' => 'SD Negeri 128 Palembang',
                'type' => 'sd',
                'recipient_category' => 'siswa',
                'default_quota' => 260,
                'address' => 'Jl. Sungai Selayur No. 18, RT 08 RW 03, Kel. Sungai Selayur, Kec. Kalidoni, Kota Palembang',
                'latitude' => -2.9685000,
                'longitude' => 104.7981000,
                'contact_phone' => '0813-7100-0007',
                'pic_name' => 'Hj. Nurhayati, S.Pd',
                'pic_phone' => '0813-7100-0007',
                'is_active' => true,
            ],
            [
                'code' => 'SCH-SS1-003',
                'name' => 'SD Negeri 129 Palembang',
                'type' => 'sd',
                'recipient_category' => 'siswa',
                'default_quota' => 244,
                'address' => 'Jl. Taqwa Mata Merah Lr. Melati, RT 15 RW 05, Kel. Sungai Selayur, Kec. Kalidoni, Kota Palembang',
                'latitude' => -2.9641000,
                'longitude' => 104.8012000,
                'contact_phone' => '0812-7889-1290',
                'pic_name' => 'Dra. Suryati, M.Pd',
                'pic_phone' => '0812-7889-1290',
                'is_active' => true,
            ],
            [
                'code' => 'SCH-SS1-004',
                'name' => 'SD Negeri 130 Palembang',
                'type' => 'sd',
                'recipient_category' => 'siswa',
                'default_quota' => 220,
                'address' => 'Jl. Mayor Zen Lr. Sukadamai, RT 19 RW 06, Kel. Sungai Selayur, Kec. Kalidoni, Kota Palembang',
                'latitude' => -2.9734000,
                'longitude' => 104.7925000,
                'contact_phone' => '0821-8890-1300',
                'pic_name' => 'Bambang Irawan, S.Pd',
                'pic_phone' => '0821-8890-1300',
                'is_active' => true,
            ],
            [
                'code' => 'SCH-SS1-005',
                'name' => 'SD Negeri 131 Palembang',
                'type' => 'sd',
                'recipient_category' => 'siswa',
                'default_quota' => 210,
                'address' => 'Jl. Sungai Batang No. 04, RT 02 RW 01, Kel. Sungai Selayur, Kec. Kalidoni, Kota Palembang',
                'latitude' => -2.9758000,
                'longitude' => 104.7891000,
                'contact_phone' => '0853-6677-1310',
                'pic_name' => 'Rina Marlina, S.Pd',
                'pic_phone' => '0853-6677-1310',
                'is_active' => true,
            ],
            [
                'code' => 'POS-SS1-001',
                'name' => 'Posyandu Melati Kalidoni',
                'type' => 'posyandu',
                'recipient_category' => 'balita_bumil',
                'default_quota' => 70,
                'address' => 'Balai Pertemuan Warga RT 14 RW 05, Kel. Sungai Selayur, Kec. Kalidoni, Kota Palembang',
                'latitude' => -2.9665000,
                'longitude' => 104.8035000,
                'contact_phone' => '0812-9988-7701',
                'pic_name' => 'Bidan Fitria, Amd.Keb',
                'pic_phone' => '0812-9988-7701',
                'is_active' => true,
            ],
            [
                'code' => 'POS-SS1-002',
                'name' => 'Posyandu Mawar Kalidoni',
                'type' => 'posyandu',
                'recipient_category' => 'balita_busui',
                'default_quota' => 60,
                'address' => 'Gedung Poskesdes RT 22 RW 07, Kel. Sungai Selayur, Kec. Kalidoni, Kota Palembang',
                'latitude' => -2.9701000,
                'longitude' => 104.8062000,
                'contact_phone' => '0813-6677-6602',
                'pic_name' => 'Kader Posyandu Marlina, S.KM',
                'pic_phone' => '0813-6677-6602',
                'is_active' => true,
            ],
        ];

        $institutions = [];
        foreach ($institutionsData as $instData) {
            $institutions[$instData['code']] = BeneficiaryInstitution::updateOrCreate(
                ['code' => $instData['code']],
                $instData
            );
        }

        // 2. DATA PENGGUNA 7 ROLE UTAMA (Personel SPPG & Guru PIC Sekolah)
        $usersData = [
            [
                'name' => 'M. Dimas Anwar',
                'email' => 'admin@mbg-selayur1.id',
                'phone' => '0811-7100-0001',
                'role' => UserRole::ADMIN,
                'beneficiary_institution_id' => null,
            ],
            [
                'name' => 'Echa Urbhita, S.Gz',
                'email' => 'gizi@mbg-selayur1.id',
                'phone' => '0812-7100-0002',
                'role' => UserRole::AHLI_GIZI,
                'beneficiary_institution_id' => null,
            ],
            [
                'name' => 'Rina Wulandari, S.E., Ak.',
                'email' => 'akuntan@mbg-selayur1.id',
                'phone' => '0813-7100-0003',
                'role' => UserRole::AKUNTAN,
                'beneficiary_institution_id' => null,
            ],
            [
                'name' => 'Chef Juna Suka Marah',
                'email' => 'dapur@mbg-selayur1.id',
                'phone' => '0812-7100-0004',
                'role' => UserRole::KEPALA_DAPUR,
                'beneficiary_institution_id' => null,
            ],
            [
                'name' => 'Agus Pratama, S.TP',
                'email' => 'lapangan@mbg-selayur1.id',
                'phone' => '0821-7100-0005',
                'role' => UserRole::ASISTEN_LAPANGAN,
                'beneficiary_institution_id' => null,
            ],
            [
                'name' => 'Dedi Kurniawan (Armada 01)',
                'email' => 'distribusi@mbg-selayur1.id',
                'phone' => '0852-7100-0006',
                'role' => UserRole::TIM_DISTRIBUSI,
                'beneficiary_institution_id' => null,
            ],
            [
                'name' => 'Hj. Nurhayati, S.Pd',
                'email' => 'pic.sdn128@mbg-selayur1.id',
                'phone' => '0813-7100-0007',
                'role' => UserRole::GURU_PIC,
                'beneficiary_institution_id' => $institutions['SCH-SS1-002']->id,
            ],
        ];

        $users = [];
        foreach ($usersData as $u) {
            $users[$u['role']->value] = User::updateOrCreate(
                ['email' => $u['email']],
                [
                    'name' => $u['name'],
                    'phone' => $u['phone'],
                    'password' => $password,
                    'role' => $u['role'],
                    'beneficiary_institution_id' => $u['beneficiary_institution_id'],
                    'is_active' => true,
                ]
            );
        }

        // 3. PARAMETER SOP BGN & KONFIGURASI OPERASIONAL
        $sopConfigs = [
            [
                'param_key' => 'pagu_anggaran_per_porsi',
                'param_name' => 'Batas Pagu Anggaran per Porsi MBG',
                'param_value' => '15000',
                'unit' => 'Rp / Porsi',
                'category' => 'pagu',
                'description' => 'Batas pagu anggaran maksimal biaya bahan baku per porsi yang ditetapkan oleh BGN dan diverifikasi oleh Akuntan.',
            ],
            [
                'param_key' => 'suhu_masak_inti_minimal',
                'param_name' => 'Suhu Inti Kematangan Masak Minimal',
                'param_value' => '75',
                'unit' => '°C',
                'category' => 'haccp_temp',
                'description' => 'Standar keamanan pangan HACCP BGN: Makanan berprotein wajib mencapai suhu inti minimal 75°C untuk mematikan patogen.',
            ],
            [
                'param_key' => 'suhu_holding_minimal',
                'param_name' => 'Suhu Penyimpanan Panas (Holding) Minimal',
                'param_value' => '60',
                'unit' => '°C',
                'category' => 'haccp_temp',
                'description' => 'Makanan siap saji harus dijaga pada holding temperature minimal 60°C sebelum diporsikan ke dalam ompreng.',
            ],
            [
                'param_key' => 'suhu_pra_distribusi_minimal',
                'param_name' => 'Suhu Muat Pra-Distribusi',
                'param_value' => '60',
                'unit' => '°C',
                'category' => 'haccp_temp',
                'description' => 'Suhu ompreng bersekat saat dinaikkan ke box isolasi termal kendaraan armada pengiriman.',
            ],
            [
                'param_key' => 'jumlah_relawan_dapur_shift',
                'param_name' => 'Jumlah Relawan Dapur per Shift',
                'param_value' => '35',
                'unit' => 'Relawan',
                'category' => 'operational',
                'description' => 'Kapasitas tenaga operasional dapur SPPG Sungai Selayur 1 yang melayani 1.414 porsi per hari.',
            ],
            [
                'param_key' => 'masa_simpan_food_sample',
                'param_name' => 'Masa Simpan Food Bank Sample',
                'param_value' => '48',
                'unit' => 'Jam (2x24 Jam)',
                'category' => 'haccp_temp',
                'description' => 'Sampel arsip makanan wajib disimpan di chiller steril bersegel minimal 2x24 jam untuk pengujian jika terjadi insiden.',
            ],
            [
                'param_key' => 'batas_waktu_tiba_maksimal',
                'param_name' => 'Batas Waktu Tiba di Sekolah',
                'param_value' => '10:30',
                'unit' => 'WIB',
                'category' => 'operational',
                'description' => 'Waktu maksimal makanan harus tiba di sekolah agar dapat dikonsumsi siswa pada jam istirahat pertama.',
            ],
        ];

        foreach ($sopConfigs as $cfg) {
            SopConfiguration::updateOrCreate(
                ['param_key' => $cfg['param_key']],
                array_merge($cfg, ['updated_by' => $users['akuntan']->id])
            );
        }

        // 4. DATA MASTER BAHAN BAKU & PEMASOK LOKAL SPPG
        $ingredientsData = [
            [
                'sku' => 'ING-KBR-001',
                'name' => 'Beras Pulen Super Ramos',
                'category' => 'karbohidrat',
                'unit' => 'kg',
                'current_stock' => 450.00,
                'min_stock_threshold' => 150.00,
                'unit_cost' => 14500.00,
            ],
            [
                'sku' => 'ING-HEW-001',
                'name' => 'Daging Ayam Broiler Fillet Segar',
                'category' => 'protein_hewani',
                'unit' => 'kg',
                'current_stock' => 180.00,
                'min_stock_threshold' => 80.00,
                'unit_cost' => 38000.00,
            ],
            [
                'sku' => 'ING-HEW-002',
                'name' => 'Telur Ayam Ras Segar Grade A',
                'category' => 'protein_hewani',
                'unit' => 'butir',
                'current_stock' => 1600.00,
                'min_stock_threshold' => 500.00,
                'unit_cost' => 1800.00,
            ],
            [
                'sku' => 'ING-NAB-001',
                'name' => 'Tempe Kedelai Murni Higienis',
                'category' => 'protein_nabati',
                'unit' => 'papan',
                'current_stock' => 120.00,
                'min_stock_threshold' => 40.00,
                'unit_cost' => 6000.00,
            ],
            [
                'sku' => 'ING-NAB-002',
                'name' => 'Tahu Putih Sutra Lembut',
                'category' => 'protein_nabati',
                'unit' => 'potong',
                'current_stock' => 600.00,
                'min_stock_threshold' => 200.00,
                'unit_cost' => 1200.00,
            ],
            [
                'sku' => 'ING-SYR-001',
                'name' => 'Buncis Segar Pilihan',
                'category' => 'sayuran',
                'unit' => 'kg',
                'current_stock' => 85.00,
                'min_stock_threshold' => 30.00,
                'unit_cost' => 16000.00,
            ],
            [
                'sku' => 'ING-SYR-002',
                'name' => 'Wortel Brastagi Segar',
                'category' => 'sayuran',
                'unit' => 'kg',
                'current_stock' => 90.00,
                'min_stock_threshold' => 30.00,
                'unit_cost' => 15000.00,
            ],
            [
                'sku' => 'ING-BUA-001',
                'name' => 'Pisang Barangan Matang Alami',
                'category' => 'buah',
                'unit' => 'sisir',
                'current_stock' => 110.00,
                'min_stock_threshold' => 40.00,
                'unit_cost' => 22000.00,
            ],
            [
                'sku' => 'ING-SUS-001',
                'name' => 'Susu UHT Segar 200ml (BGN)',
                'category' => 'susu',
                'unit' => 'kotak',
                'current_stock' => 1500.00,
                'min_stock_threshold' => 500.00,
                'unit_cost' => 4200.00,
            ],
            [
                'sku' => 'ING-BMN-001',
                'name' => 'Minyak Goreng Sawit Higienis',
                'category' => 'bumbu_minyak',
                'unit' => 'liter',
                'current_stock' => 80.00,
                'min_stock_threshold' => 25.00,
                'unit_cost' => 17500.00,
            ],
            [
                'sku' => 'ING-BMN-002',
                'name' => 'Bumbu Halus Rempah Tradisional',
                'category' => 'bumbu_minyak',
                'unit' => 'kg',
                'current_stock' => 35.00,
                'min_stock_threshold' => 15.00,
                'unit_cost' => 28000.00,
            ],
        ];

        $ingredients = [];
        foreach ($ingredientsData as $ingData) {
            $ingredients[$ingData['sku']] = Ingredient::updateOrCreate(
                ['sku' => $ingData['sku']],
                $ingData
            );
        }

        // 5. TRANSAKSI LOG MUTASI GUDANG (Penerimaan Inbound & Pengeluaran Masak)
        $transactionsData = [
            [
                'reference_no' => 'INB/SS1/20261026/001',
                'ingredient_id' => $ingredients['ING-KBR-001']->id,
                'type' => 'inbound_purchase',
                'quantity' => 250.00,
                'unit_price' => 14500.00,
                'total_cost' => 3625000.00,
                'supplier_name' => 'Koperasi Lumbung Pangan Kalidoni',
                'freshness_condition' => 'sangat_segar',
                'recorded_by' => $users['asisten_lapangan']->id,
                'verified_by_accountant' => $users['akuntan']->id,
                'notes' => 'Penerimaan beras karung 25kg kondisi kering bersih sesuai SOP gudang.',
            ],
            [
                'reference_no' => 'INB/SS1/20261026/002',
                'ingredient_id' => $ingredients['ING-HEW-001']->id,
                'type' => 'inbound_purchase',
                'quantity' => 120.00,
                'unit_price' => 38000.00,
                'total_cost' => 4560000.00,
                'supplier_name' => 'Peternakan Mandiri Unggas Sukarami',
                'freshness_condition' => 'sangat_segar',
                'recorded_by' => $users['asisten_lapangan']->id,
                'verified_by_accountant' => $users['akuntan']->id,
                'notes' => 'Ayam fillet chilled rantai dingin suhu 3°C, kemasan vacuum seal higienis.',
            ],
            [
                'reference_no' => 'INB/SS1/20261026/003',
                'ingredient_id' => $ingredients['ING-SUS-001']->id,
                'type' => 'inbound_purchase',
                'quantity' => 1500.00,
                'unit_price' => 4200.00,
                'total_cost' => 6300000.00,
                'supplier_name' => 'PT Indolakto Distribusi Palembang',
                'freshness_condition' => 'sangat_segar',
                'recorded_by' => $users['asisten_lapangan']->id,
                'verified_by_accountant' => $users['akuntan']->id,
                'notes' => 'Susu UHT 200ml kemasan kotak steril, masa kedaluwarsa 9 bulan ke depan.',
            ],
        ];

        foreach ($transactionsData as $tx) {
            InventoryTransaction::updateOrCreate(
                ['reference_no' => $tx['reference_no']],
                $tx
            );
        }

        // 6. PERENCANAAN MENU HARIAN (Sesuai 1.414 Porsi, Validasi PAGU Akuntan & Approval Kepala Dapur)
        $operationalDate = Carbon::today();

        $dailyMenu = DailyMenu::updateOrCreate(
            ['operational_date' => $operationalDate],
            [
                'menu_code' => 'MNU-SS1-' . $operationalDate->format('Ymd'),
                'menu_name' => 'Paket Gizi Nusantara: Ayam Panggang Madu, Tempe Bacem, Sayur Buncis Wortel, Pisang & Susu UHT',
                'target_portions' => 1414,
                'recipient_category' => 'siswa',
                'calories_kcal' => 685.00,
                'protein_gram' => 28.50,
                'carbs_gram' => 84.00,
                'fat_gram' => 21.00,
                'fiber_gram' => 8.20,
                'estimated_cost_per_portion' => 14850.00,
                'pagu_status' => 'sesuai_pagu',
                'pagu_notes' => 'Diverifikasi oleh Akuntan: Biaya per porsi Rp14.850 berada di bawah batas pagu resmi BGN Rp15.000.',
                'verified_by_accountant_id' => $users['akuntan']->id,
                'pagu_verified_at' => $operationalDate->copy()->setHour(6)->setMinute(15),
                'status' => 'approved',
                'created_by' => $users['ahli_gizi']->id,
                'approved_by' => $users['kepala_dapur']->id,
                'approved_at' => $operationalDate->copy()->setHour(6)->setMinute(30),
                'approval_notes' => 'Disetujui untuk siklus masak hari ini. 35 relawan dapur siap bertugas dengan APD lengkap.',
            ]
        );

        // Komposisi bahan baku per porsi (Gramasi)
        $menuItems = [
            [
                'ingredient_id' => $ingredients['ING-KBR-001']->id,
                'qty_per_portion' => 0.1500, // 150 gram beras
                'total_planned_qty' => 212.10,
                'actual_used_qty' => 212.00,
                'preparation_notes' => 'Cuci 3 kali bilas higienis, masak menggunakan combi steamer dapur SPPG.',
            ],
            [
                'ingredient_id' => $ingredients['ING-HEW-001']->id,
                'qty_per_portion' => 0.0800, // 80 gram fillet ayam
                'total_planned_qty' => 113.12,
                'actual_used_qty' => 113.00,
                'preparation_notes' => 'Marinasi kecap rempah madu, panggang hingga suhu inti minimal 75°C tercapai.',
            ],
            [
                'ingredient_id' => $ingredients['ING-NAB-001']->id,
                'qty_per_portion' => 0.0500, // 50 gram tempe
                'total_planned_qty' => 70.70,
                'actual_used_qty' => 70.50,
                'preparation_notes' => 'Potong ketebalan 1.5 cm, bacem bumbu ketumbar gula kelapa.',
            ],
            [
                'ingredient_id' => $ingredients['ING-SYR-001']->id,
                'qty_per_portion' => 0.0450, // 45 gram buncis
                'total_planned_qty' => 63.63,
                'actual_used_qty' => 63.50,
                'preparation_notes' => 'Potong serong 3 cm, blanching cepat agar renyah segar.',
            ],
            [
                'ingredient_id' => $ingredients['ING-SYR-002']->id,
                'qty_per_portion' => 0.0450, // 45 gram wortel
                'total_planned_qty' => 63.63,
                'actual_used_qty' => 63.50,
                'preparation_notes' => 'Potong julienne serasi dengan buncis.',
            ],
            [
                'ingredient_id' => $ingredients['ING-SUS-001']->id,
                'qty_per_portion' => 1.0000, // 1 kotak susu
                'total_planned_qty' => 1414.00,
                'actual_used_qty' => 1414.00,
                'preparation_notes' => 'Susu steril 200ml diselipkan rapi pada kantong khusus ompreng.',
            ],
        ];

        foreach ($menuItems as $item) {
            DailyMenuItem::updateOrCreate(
                [
                    'daily_menu_id' => $dailyMenu->id,
                    'ingredient_id' => $item['ingredient_id'],
                ],
                $item
            );
        }

        // 7. MONITORING PRODUKSI DAPUR & PEMORSIAN OMPRENG (Target 1.414 Porsi, 35 Relawan)
        $productionRun = ProductionRun::updateOrCreate(
            ['daily_menu_id' => $dailyMenu->id],
            [
                'planned_portions' => 1414,
                'actual_good_portions' => 1414,
                'rejected_damaged_portions' => 0,
                'portion_variance' => 0,
                'cooking_started_at' => $operationalDate->copy()->setHour(4)->setMinute(30),
                'cooking_finished_at' => $operationalDate->copy()->setHour(7)->setMinute(30),
                'packaging_finished_at' => $operationalDate->copy()->setHour(8)->setMinute(45),
                'status' => 'ready_for_dispatch',
                'supervised_by' => $users['kepala_dapur']->id,
                'variance_reason' => 'Pemorsian ompreng berjalan lancar sesuai standar gramasi BGN.',
                'operational_obstacle' => 'Tidak ada kendala kritis. Arus air dan api kompor bertekanan stabil.',
                'action_taken' => 'Seluruh 1.414 ompreng diperiksa segel dan penutupnya sebelum masuk container armada.',
                'volunteers_count' => 35,
            ]
        );

        // 8. MONITORING KEAMANAN PANGAN & APD SOP BGN (HACCP Suhu >=75°C, Holding >=60°C, 35 Relawan)
        $qcInspection = QcInspection::updateOrCreate(
            ['production_run_id' => $productionRun->id],
            [
                'inspector_id' => $users['ahli_gizi']->id,
                'cooking_core_temp_c' => 84.50, // >= 75°C (Lolos)
                'holding_temp_c' => 67.80,      // >= 60°C (Lolos)
                'pre_dispatch_temp_c' => 63.50, // >= 60°C (Lolos)
                'total_kitchen_staff_present' => 35,
                'apd_mask_compliant' => true,
                'apd_hairnet_compliant' => true,
                'apd_gloves_compliant' => true,
                'apd_apron_compliant' => true,
                'apd_safety_shoes_compliant' => true,
                'kitchen_sanitation_passed' => true,
                'food_sample_retained' => true,
                'food_sample_code' => 'FS-SS1-' . $operationalDate->format('Ymd') . '-A1',
                'qc_verdict' => 'passed',
                'corrective_action' => 'Kepatuhan SOP BGN 100%. Sampel arsip 2x24 jam telah disimpan di chiller bertiket.',
                'corrective_action_status' => 'resolved',
                'resolved_by' => $users['kepala_dapur']->id,
                'inspected_at' => $operationalDate->copy()->setHour(8)->setMinute(30),
            ]
        );

        // 8b. MONITORING KEPATUHAN APD RELAWAN DAPUR (35 Relawan - Form Ceklis Ahli Gizi)
        ApdInspection::updateOrCreate(
            [
                'inspection_date' => $operationalDate,
                'shift' => 'Pagi (05:00 - 11:00)',
            ],
            [
                'inspector_id' => $users['ahli_gizi']->id,
                'production_run_id' => $productionRun->id,
                'total_volunteers' => 35,
                'volunteers_checked' => 35,
                'apron_compliant_count' => 35,
                'hairnet_compliant_count' => 35,
                'mask_compliant_count' => 34,
                'gloves_compliant_count' => 35,
                'safety_shoes_compliant_count' => 35,
                'clean_nails_compliant' => true,
                'no_jewelry_compliant' => true,
                'handwash_facility_ready' => true,
                'compliance_percentage' => 99.43,
                'status' => 'patuh_penuh',
                'notes' => 'Pemeriksaan rutin jam 06:15 WIB oleh Ahli Gizi Echa Urbhita, S.Gz. Sebanyak 35 relawan dapur hadir lengkap. 1 relawan masker sedikit melorot saat menumis dan langsung diganti masker baru.',
                'corrective_action' => 'Peringatan persuasif dan penggantian masker higienis baru di pos pengolahan.',
                'verified_by_head_chef_id' => $users['kepala_dapur']->id,
            ]
        );

        // 9. MONITORING DISTRIBUSI: 7 Titik Rute (5 Sekolah + 2 Posyandu = Total 1.414 Porsi)
        $deliveriesData = [
            [
                'institution' => $institutions['SCH-SS1-001'], // Ponpes Muqimus Sunnah
                'waybill_number' => 'SJ/MBG-SS1/' . $operationalDate->format('Ymd') . '/001',
                'vehicle_plate_number' => 'BG 8122 KL',
                'portions' => 350,
                'departed_at' => $operationalDate->copy()->setHour(9)->setMinute(0),
                'arrived_at' => $operationalDate->copy()->setHour(9)->setMinute(35),
                'status' => 'arrived',
                'courier_notes' => 'Rute Jalur 1 Mayor Zen lancar, box makanan tiba kondisi segel utuh dan hangat.',
                'confirmed' => false,
            ],
            [
                'institution' => $institutions['SCH-SS1-002'], // SDN 128 Palembang
                'waybill_number' => 'SJ/MBG-SS1/' . $operationalDate->format('Ymd') . '/002',
                'vehicle_plate_number' => 'BG 8123 KL',
                'portions' => 260,
                'departed_at' => $operationalDate->copy()->setHour(9)->setMinute(10),
                'arrived_at' => $operationalDate->copy()->setHour(9)->setMinute(40),
                'status' => 'confirmed_clean',
                'courier_notes' => 'Tiba tepat waktu, diserahkan langsung kepada Ibu Hj. Nurhayati, S.Pd.',
                'confirmed' => true,
                'confirmation_data' => [
                    'confirmed_by' => $users['guru_pic']->id,
                    'received_good_portions' => 260,
                    'received_damaged_or_missing_portions' => 0,
                    'reception_variance' => 0,
                    'aroma_test' => 'normal_segar',
                    'taste_test' => 'normal_lezat',
                    'color_visual_test' => 'cerah_normal',
                    'texture_test' => 'baik_sesuai',
                    'arrival_temperature_c' => 58.50,
                    'is_fit_for_consumption' => true,
                    'complaint_severity' => 'none',
                    'feedback_or_complaint' => 'Uji organoleptik sangat baik: lauk hangat, ayam empuk harum madu, sayur segar renyah. Telah dibagikan ke seluruh siswa kelas 1-6.',
                ],
            ],
            [
                'institution' => $institutions['SCH-SS1-003'], // SDN 129 Palembang
                'waybill_number' => 'SJ/MBG-SS1/' . $operationalDate->format('Ymd') . '/003',
                'vehicle_plate_number' => 'BG 8124 KL',
                'portions' => 244,
                'departed_at' => $operationalDate->copy()->setHour(9)->setMinute(15),
                'arrived_at' => $operationalDate->copy()->setHour(9)->setMinute(48),
                'status' => 'confirmed_with_issue',
                'courier_notes' => 'Rute Taqwa Mata Merah sedikit padat kendaraan proyek.',
                'confirmed' => true,
                'confirmation_data' => [
                    'confirmed_by' => $users['asisten_lapangan']->id, // Perwakilan Lapangan
                    'received_good_portions' => 242,
                    'received_damaged_or_missing_portions' => 2,
                    'reception_variance' => -2,
                    'aroma_test' => 'normal_segar',
                    'taste_test' => 'normal_lezat',
                    'color_visual_test' => 'cerah_normal',
                    'texture_test' => 'baik_sesuai',
                    'arrival_temperature_c' => 56.80,
                    'is_fit_for_consumption' => true,
                    'complaint_severity' => 'medium',
                    'feedback_or_complaint' => '2 ompreng mengalami retak tutup akibat guncangan jalan sehingga kuah merembes. Sisa 242 porsi dalam kondisi prima dan layak konsumsi.',
                ],
            ],
            [
                'institution' => $institutions['SCH-SS1-004'], // SDN 130 Palembang
                'waybill_number' => 'SJ/MBG-SS1/' . $operationalDate->format('Ymd') . '/004',
                'vehicle_plate_number' => 'BG 8122 KL',
                'portions' => 220,
                'departed_at' => $operationalDate->copy()->setHour(9)->setMinute(20),
                'arrived_at' => null,
                'status' => 'in_transit',
                'courier_notes' => 'Armada 01 bergerak menuju SDN 130 setelah titik Muqimus Sunnah.',
                'confirmed' => false,
            ],
            [
                'institution' => $institutions['SCH-SS1-005'], // SDN 131 Palembang
                'waybill_number' => 'SJ/MBG-SS1/' . $operationalDate->format('Ymd') . '/005',
                'vehicle_plate_number' => 'BG 8123 KL',
                'portions' => 210,
                'departed_at' => $operationalDate->copy()->setHour(9)->setMinute(25),
                'arrived_at' => null,
                'status' => 'in_transit',
                'courier_notes' => 'Armada 02 bergerak menuju SDN 131 Sungai Batang.',
                'confirmed' => false,
            ],
            [
                'institution' => $institutions['POS-SS1-001'], // Posyandu Melati (Bumil & Balita)
                'waybill_number' => 'SJ/MBG-SS1/' . $operationalDate->format('Ymd') . '/006',
                'vehicle_plate_number' => 'BG 8125 KL',
                'portions' => 70,
                'departed_at' => $operationalDate->copy()->setHour(8)->setMinute(50),
                'arrived_at' => $operationalDate->copy()->setHour(9)->setMinute(15),
                'status' => 'confirmed_clean',
                'courier_notes' => 'Pengantaran motor box higienis ke Balai Warga Posyandu Melati.',
                'confirmed' => true,
                'confirmation_data' => [
                    'confirmed_by' => $users['asisten_lapangan']->id,
                    'received_good_portions' => 70,
                    'received_damaged_or_missing_portions' => 0,
                    'reception_variance' => 0,
                    'aroma_test' => 'normal_segar',
                    'taste_test' => 'normal_lezat',
                    'color_visual_test' => 'cerah_normal',
                    'texture_test' => 'baik_sesuai',
                    'arrival_temperature_c' => 59.20,
                    'is_fit_for_consumption' => true,
                    'complaint_severity' => 'none',
                    'feedback_or_complaint' => 'Porsi gizi untuk ibu hamil dan balita diterima lengkap 70 porsi. Sayuran lunak dan tekstur sangat ramah balita.',
                ],
            ],
            [
                'institution' => $institutions['POS-SS1-002'], // Posyandu Mawar (Busui & Balita)
                'waybill_number' => 'SJ/MBG-SS1/' . $operationalDate->format('Ymd') . '/007',
                'vehicle_plate_number' => 'BG 8125 KL',
                'portions' => 60,
                'departed_at' => $operationalDate->copy()->setHour(9)->setMinute(0),
                'arrived_at' => $operationalDate->copy()->setHour(9)->setMinute(25),
                'status' => 'confirmed_clean',
                'courier_notes' => 'Pengantaran motor box higienis titik kedua Posyandu Mawar.',
                'confirmed' => true,
                'confirmation_data' => [
                    'confirmed_by' => $users['asisten_lapangan']->id,
                    'received_good_portions' => 60,
                    'received_damaged_or_missing_portions' => 0,
                    'reception_variance' => 0,
                    'aroma_test' => 'normal_segar',
                    'taste_test' => 'normal_lezat',
                    'color_visual_test' => 'cerah_normal',
                    'texture_test' => 'baik_sesuai',
                    'arrival_temperature_c' => 58.70,
                    'is_fit_for_consumption' => true,
                    'complaint_severity' => 'none',
                    'feedback_or_complaint' => 'Porsi untuk balita dan ibu menyusui diterima lengkap 60 ompreng. Rasa pas dan bersih.',
                ],
            ],
        ];

        $deliveries = [];
        foreach ($deliveriesData as $delivData) {
            $delivery = Delivery::updateOrCreate(
                ['waybill_number' => $delivData['waybill_number']],
                [
                    'production_run_id' => $productionRun->id,
                    'beneficiary_institution_id' => $delivData['institution']->id,
                    'courier_id' => $users['tim_distribusi']->id,
                    'vehicle_plate_number' => $delivData['vehicle_plate_number'],
                    'allocated_portions' => $delivData['portions'],
                    'dispatched_portions' => $delivData['portions'],
                    'departed_at' => $delivData['departed_at'],
                    'arrived_at' => $delivData['arrived_at'],
                    'status' => $delivData['status'],
                    'courier_notes' => $delivData['courier_notes'],
                ]
            );

            $deliveries[$delivData['waybill_number']] = $delivery;

            if ($delivData['confirmed']) {
                $cData = $delivData['confirmation_data'];
                DeliveryConfirmation::updateOrCreate(
                    ['delivery_id' => $delivery->id],
                    array_merge($cData, [
                        'confirmed_at' => $delivData['arrived_at']->copy()->addMinutes(12),
                    ])
                );
            }
        }

        // 10. TIKET KELUHAN DIGITAL (Fitur 7 - Nomor Tiket Otomatis & Penanganan oleh Aslap / Kepala Dapur)
        $complaintTicket = ComplaintTicket::updateOrCreate(
            ['ticket_number' => 'TKT-' . $operationalDate->format('Ymd') . '-001'],
            [
                'delivery_id' => $deliveries['SJ/MBG-SS1/' . $operationalDate->format('Ymd') . '/003']->id,
                'beneficiary_institution_id' => $institutions['SCH-SS1-003']->id,
                'reported_by' => $users['asisten_lapangan']->id,
                'category' => 'ompreng_rusak',
                'severity' => 'sedang',
                'description' => 'Terdapat 2 ompreng mengalami retak klip pengunci saat tiba di SDN 129 Palembang, kuah buncis merembes ke tray pelindung. Dibutuhkan penggantian ompreng baru untuk siklus esok hari.',
                'photo_path' => null,
                'status' => 'in_progress',
                'resolved_by' => $users['asisten_lapangan']->id,
                'resolution_notes' => 'Asisten Lapangan telah menyiapkan 2 ompreng cadangan bersegel steril dari pool SPPG Sungai Selayur 1 untuk dikirimkan dan diganti.',
                'resolved_at' => null,
            ]
        );

        // 11. AUDIT TRAIL / LOG AKTIVITAS (Fitur 2 & Kriteria Penerimaan Non-Fungsional)
        $auditLogs = [
            [
                'user_id' => $users['admin']->id,
                'user_name' => 'M. Dimas Anwar',
                'role' => 'admin',
                'action' => 'setup',
                'module' => 'master_data',
                'description' => 'Konfigurasi awal master 5 sekolah, 2 posyandu (total kuota 1.414 porsi), standar porsi, dan 35 relawan dapur SPPG Sungai Selayur 1.',
                'created_at' => $operationalDate->copy()->setHour(5)->setMinute(0),
            ],
            [
                'user_id' => $users['ahli_gizi']->id,
                'user_name' => 'Echa Urbhita, S.Gz',
                'role' => 'ahli_gizi',
                'action' => 'create',
                'module' => 'menu_planning',
                'description' => 'Menyusun draf rencana menu ' . $dailyMenu->menu_code . ' dengan target 1.414 porsi dan estimasi biaya Rp14.850/porsi.',
                'created_at' => $operationalDate->copy()->setHour(5)->setMinute(45),
            ],
            [
                'user_id' => $users['akuntan']->id,
                'user_name' => 'Rina Wulandari, S.E., Ak.',
                'role' => 'akuntan',
                'action' => 'verify',
                'module' => 'budget_pagu',
                'description' => 'Verifikasi kesesuaian anggaran menu ' . $dailyMenu->menu_code . ': Biaya Rp14.850/porsi memenuhi standar pagu (Maks Rp15.000). Status: Sesuai PAGU.',
                'created_at' => $operationalDate->copy()->setHour(6)->setMinute(15),
            ],
            [
                'user_id' => $users['kepala_dapur']->id,
                'user_name' => 'Chef Hendra Wijaya',
                'role' => 'kepala_dapur',
                'action' => 'approve',
                'module' => 'menu_approval',
                'description' => 'Memberikan persetujuan (approval) rencana menu harian ' . $dailyMenu->menu_code . ' untuk siklus masak 1.414 ompreng.',
                'created_at' => $operationalDate->copy()->setHour(6)->setMinute(30),
            ],
            [
                'user_id' => $users['ahli_gizi']->id,
                'user_name' => 'Echa Urbhita, S.Gz',
                'role' => 'ahli_gizi',
                'action' => 'inspect',
                'module' => 'quality_control',
                'description' => 'Pemeriksaan keamanan pangan HACCP: Suhu inti masak 84.5°C, holding 67.8°C, kepatuhan 5 APD 35 relawan dapur 100%, sampel arsip 2x24 jam tersimpan.',
                'created_at' => $operationalDate->copy()->setHour(8)->setMinute(30),
            ],
            [
                'user_id' => $users['tim_distribusi']->id,
                'user_name' => 'Dedi Kurniawan (Armada 01)',
                'role' => 'tim_distribusi',
                'action' => 'dispatch',
                'module' => 'distribution',
                'description' => 'Menerbitkan surat jalan dan memberangkatkan armada pengiriman ke 7 titik distribusi wilayah Kalidoni.',
                'created_at' => $operationalDate->copy()->setHour(9)->setMinute(5),
            ],
            [
                'user_id' => $users['guru_pic']->id,
                'user_name' => 'Hj. Nurhayati, S.Pd',
                'role' => 'guru_pic',
                'action' => 'confirm',
                'module' => 'reception',
                'description' => 'Konfirmasi penerimaan 260 porsi di SDN 128 Palembang dan pengisian formulir uji organoleptik: Aroma normal segar, rasa lezat, layak konsumsi.',
                'created_at' => $operationalDate->copy()->setHour(9)->setMinute(52),
            ],
        ];

        foreach ($auditLogs as $log) {
            ActivityLog::create(array_merge($log, ['ip_address' => '127.0.0.1']));
        }
    }
}
