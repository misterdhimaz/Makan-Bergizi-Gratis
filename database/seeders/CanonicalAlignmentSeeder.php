<?php

namespace Database\Seeders;

use App\Enums\DeliveryStatus;
use App\Enums\UserRole;
use App\Models\BeneficiaryInstitution;
use App\Models\ComplaintHistory;
use App\Models\ComplaintTicket;
use App\Models\DailyMenu;
use App\Models\DataRevision;
use App\Models\DeliveryRoute;
use App\Models\DeliveryStatusLog;
use App\Models\FoodSafetyCheck;
use App\Models\FoodSafetyFinding;
use App\Models\Ingredient;
use App\Models\InventoryTransaction;
use App\Models\Location;
use App\Models\User;
use App\Services\AuditLogService;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class CanonicalAlignmentSeeder extends Seeder
{
    public function run(): void
    {
        AuditLogService::$enabled = false;

        try {
            $locations = Location::query()->get()->keyBy('name');
            $this->alignUsers($locations);
            $this->alignDeliveries($locations);
            $this->alignComplaints($locations);
            $this->alignDataRevisions($locations);
            $this->alignFoodSafety();
            $this->alignMenu27Procurement();
        } finally {
            AuditLogService::$enabled = true;
        }
    }

    private function alignUsers($locations): void
    {
        $now = Carbon::parse('2026-11-26 08:50:00', 'Asia/Jakarta');
        $password = Hash::make('password');

        // Hapus guru_pic duplikat non-kanonik agar tepat 7 Guru PIC aktif (1 per lokasi) & total 17 akun
        User::whereIn('username', [
            'ahmad.fauzi',
            'fitria.handayani',
            'marlina.sari',
            'suryati.pic',
            'dewi.sartika',
        ])->delete();

        $guruPics = [
            ['username' => 'rahmi.safitra', 'name' => 'Rahmi Safitra', 'location' => 'Posyandu Mawar Kalidoni', 'last_login_at' => $now->copy()->subHours(2)],
            ['username' => 'lestari.wulandari', 'name' => 'Lestari Wulandari', 'location' => 'Posyandu Melati Kalidoni', 'last_login_at' => $now->copy()->subHours(3)],
            ['username' => 'yuni.lestari', 'name' => 'Yuni Lestari', 'location' => 'SD Negeri 129 Palembang', 'last_login_at' => $now->copy()->subHours(1)],
            ['username' => 'agus.prasetyo', 'name' => 'Agus Prasetyo', 'location' => 'SD Negeri 130 Palembang', 'last_login_at' => $now->copy()->subDay()],
            ['username' => 'dewi.kartika', 'name' => 'Dewi Kartika', 'location' => 'SD Negeri 131 Palembang', 'last_login_at' => $now->copy()->subDay()],
            ['username' => 'siti.aminah', 'name' => 'Siti Aminah', 'location' => 'SD Negeri 132 Palembang', 'last_login_at' => null],
            ['username' => 'rina.marlina', 'name' => 'Rina Marlina', 'location' => 'Pesantren Al-Falah Palembang', 'last_login_at' => $now->copy()->subHours(1)],
        ];

        foreach ($guruPics as $pic) {
            User::updateOrCreate(
                ['username' => $pic['username']],
                [
                    'name' => $pic['name'],
                    'email' => $pic['username'].'@mbg-selayur1.id',
                    'password' => $password,
                    'role' => UserRole::GURU_PIC,
                    'location_id' => $locations[$pic['location']]->id ?? null,
                    'is_active' => true,
                    'must_change_password' => false,
                    'failed_login_attempts' => 0,
                    'locked_until' => null,
                    'last_login_at' => $pic['last_login_at'],
                ]
            );
        }

        // Pastikan seluruh akun demo aktif dapat login dengan password 'password' di env local/testing
        User::where('is_active', true)->update([
            'password' => $password,
            'must_change_password' => false,
        ]);
    }

    private function alignDeliveries($locations): void
    {
        $fajar = User::where('username', 'fajar.nugroho')->first();
        $rahmi = User::where('username', 'rahmi.safitra')->first();
        $yuni = User::where('username', 'yuni.lestari')->first();
        $rina = User::where('username', 'rina.marlina')->first();

        // Bersihkan rute 26 Nov non-kanonik (misal SJ/SS1/* atau #DST-*)
        DeliveryRoute::whereDate('operational_date', '2026-11-26')
            ->where(function ($q) {
                $q->where('waybill_number', 'like', 'SJ/%')
                    ->orWhere('waybill_number', 'like', '#DST-%');
            })
            ->delete();

        $todayRoutes = [
            [
                'waybill' => 'DST-20261126-01',
                'kelompok' => 'Rute 1',
                'location' => 'Posyandu Mawar Kalidoni',
                'porsi' => 60,
                'planned_dep' => '07:35',
                'dep' => '2026-11-26 07:35:00',
                'sched_arr' => '08:00',
                'arr' => '2026-11-26 07:55:00',
                'status' => DeliveryStatus::DITERIMA,
                'courier_name' => 'Pak Andi',
                'plate' => 'BG 8101 KL',
                'confirmed' => 60,
                'confirmer' => $rahmi?->id,
                'confirmed_at' => '2026-11-26 07:58:00',
                'packaging' => 'baik',
                'organoleptic' => 'Layak',
                'notes' => 'Diterima 60/60 porsi dalam kondisi baik.',
            ],
            [
                'waybill' => 'DST-20261126-02',
                'kelompok' => 'Rute 2',
                'location' => 'SD Negeri 129 Palembang',
                'porsi' => 260,
                'planned_dep' => '07:45',
                'dep' => '2026-11-26 07:45:00',
                'sched_arr' => '08:20',
                'arr' => '2026-11-26 08:15:00',
                'status' => DeliveryStatus::DITERIMA,
                'courier_name' => 'Pak Budi',
                'plate' => 'BG 8104 KL',
                'confirmed' => 258,
                'confirmer' => $yuni?->id,
                'confirmed_at' => '2026-11-26 08:18:00',
                'packaging' => 'baik',
                'organoleptic' => 'Layak',
                'notes' => 'Diterima 258/260 porsi (selisih -2).',
            ],
            [
                'waybill' => 'DST-20261126-03',
                'kelompok' => 'Rute 3',
                'location' => 'Posyandu Melati Kalidoni',
                'porsi' => 70,
                'planned_dep' => '07:50',
                'dep' => '2026-11-26 07:50:00',
                'sched_arr' => '08:20',
                'arr' => '2026-11-26 08:20:00',
                'status' => DeliveryStatus::TIBA,
                'courier_name' => 'Pak Candra',
                'plate' => 'BG 8107 KL',
                'confirmed' => null,
                'confirmer' => null,
                'confirmed_at' => null,
                'packaging' => null,
                'organoleptic' => null,
                'notes' => 'Menunggu konfirmasi Guru PIC.',
            ],
            [
                'waybill' => 'DST-20261126-04',
                'kelompok' => 'Rute 4',
                'location' => 'Pesantren Al-Falah Palembang',
                'porsi' => 350,
                'planned_dep' => '08:05',
                'dep' => '2026-11-26 08:05:00',
                'sched_arr' => '08:30',
                'arr' => '2026-11-26 08:40:00',
                'status' => DeliveryStatus::BERMASALAH,
                'courier_name' => 'Pak Gunawan',
                'plate' => 'BG 8123 KL',
                'confirmed' => null,
                'confirmer' => null,
                'confirmed_at' => null,
                'packaging' => null,
                'organoleptic' => null,
                'notes' => '4 ompreng tumis buncis tumpah saat pengantaran pagi.',
            ],
            [
                'waybill' => 'DST-20261126-05',
                'kelompok' => 'Rute 5',
                'location' => 'SD Negeri 130 Palembang',
                'porsi' => 220,
                'planned_dep' => '08:15',
                'dep' => '2026-11-26 08:15:00',
                'sched_arr' => '08:45',
                'arr' => null,
                'status' => DeliveryStatus::BERANGKAT,
                'courier_name' => 'Pak Dedi',
                'plate' => 'BG 8112 KL',
                'confirmed' => null,
                'confirmer' => null,
                'confirmed_at' => null,
                'packaging' => null,
                'organoleptic' => null,
                'notes' => 'Dalam perjalanan menuju SD Negeri 130.',
            ],
            [
                'waybill' => 'DST-20261126-06',
                'kelompok' => 'Rute 6',
                'location' => 'SD Negeri 131 Palembang',
                'porsi' => 210,
                'planned_dep' => '08:30',
                'dep' => '2026-11-26 08:30:00',
                'sched_arr' => '09:00',
                'arr' => null,
                'status' => DeliveryStatus::BERANGKAT,
                'courier_name' => 'Pak Eko',
                'plate' => 'BG 8115 KL',
                'confirmed' => null,
                'confirmer' => null,
                'confirmed_at' => null,
                'packaging' => null,
                'organoleptic' => null,
                'notes' => 'Dalam perjalanan menuju SD Negeri 131.',
            ],
            [
                'waybill' => 'DST-20261126-07',
                'kelompok' => 'Rute 7',
                'location' => 'SD Negeri 132 Palembang',
                'porsi' => 244,
                'planned_dep' => '09:00',
                'dep' => null,
                'sched_arr' => '09:30',
                'arr' => null,
                'status' => DeliveryStatus::DISIAPKAN,
                'courier_name' => 'Pak Fajar',
                'plate' => 'BG 8119 KL',
                'confirmed' => null,
                'confirmer' => null,
                'confirmed_at' => null,
                'packaging' => null,
                'organoleptic' => null,
                'notes' => 'Jadwal keberangkatan pukul 09:00 WIB.',
            ],
        ];

        foreach ($todayRoutes as $r) {
            $route = DeliveryRoute::updateOrCreate(
                ['waybill_number' => $r['waybill']],
                [
                    'operational_date' => '2026-11-26',
                    'kelompok_rute' => $r['kelompok'],
                    'planned_departure_time' => $r['planned_dep'],
                    'scheduled_arrival_time' => $r['sched_arr'],
                    'location_id' => $locations[$r['location']]->id,
                    'status' => $r['status'],
                    'dispatched_portions' => $r['porsi'],
                    'confirmed_portions' => $r['confirmed'],
                    'courier_id' => $fajar?->id,
                    'courier_name' => $r['courier_name'],
                    'vehicle_plate' => $r['plate'],
                    'confirmed_by' => $r['confirmer'],
                    'prepared_at' => Carbon::parse('2026-11-26 06:30:00', 'Asia/Jakarta'),
                    'departed_at' => $r['dep'] ? Carbon::parse($r['dep'], 'Asia/Jakarta') : null,
                    'arrived_at' => $r['arr'] ? Carbon::parse($r['arr'], 'Asia/Jakarta') : null,
                    'confirmed_at' => $r['confirmed_at'] ? Carbon::parse($r['confirmed_at'], 'Asia/Jakarta') : null,
                    'packaging_condition' => $r['packaging'],
                    'organoleptic_taste' => $r['organoleptic'] ? 'normal' : null,
                    'organoleptic_aroma' => $r['organoleptic'] ? 'normal' : null,
                    'organoleptic_texture' => $r['organoleptic'] ? 'normal' : null,
                    'organoleptic_color' => $r['organoleptic'] ? 'normal' : null,
                    'organoleptic_status' => $r['organoleptic'],
                    'notes' => $r['notes'],
                ]
            );

            if ($route->statusLogs()->count() === 0) {
                DeliveryStatusLog::create([
                    'delivery_route_id' => $route->id,
                    'status' => $r['status'],
                    'user_id' => $fajar?->id,
                    'notes' => 'Status awal: '.$r['status']->label().' (dicatat Fajar Nugroho)',
                    'created_at' => Carbon::parse($r['arr'] ?? $r['dep'] ?? '2026-11-26 06:30:00', 'Asia/Jakarta'),
                ]);
            }
        }

        // Riwayat Pesantren Al-Falah (25, 24, 21, 20 Nov — semua 350/350, Rina Marlina, Layak)
        $pesantren = $locations['Pesantren Al-Falah Palembang'];
        DeliveryRoute::where('location_id', $pesantren->id)
            ->where('operational_date', '<', '2026-11-26')
            ->delete();

        $pesantrenHistory = [
            ['date' => '2026-11-25', 'waybill' => 'DST-20261125-04', 'arr' => '07:55', 'note' => 'Tiba tepat waktu, 350/350 porsi layak konsumsi.'],
            ['date' => '2026-11-24', 'waybill' => 'DST-20261124-04', 'arr' => '07:50', 'note' => 'Tiba lebih awal, kondisi ompreng baik.'],
            ['date' => '2026-11-21', 'waybill' => 'DST-20261121-04', 'arr' => '08:20', 'note' => 'Terlambat 20 menit karena hujan deras, 350/350 porsi layak.'],
            ['date' => '2026-11-20', 'waybill' => 'DST-20261120-04', 'arr' => '07:58', 'note' => 'Diterima lengkap dan tepat waktu.'],
        ];

        foreach ($pesantrenHistory as $h) {
            DeliveryRoute::updateOrCreate(
                ['waybill_number' => $h['waybill']],
                [
                    'operational_date' => $h['date'],
                    'kelompok_rute' => 'Rute 4',
                    'planned_departure_time' => '07:35',
                    'scheduled_arrival_time' => '08:00',
                    'location_id' => $pesantren->id,
                    'status' => DeliveryStatus::DITERIMA,
                    'dispatched_portions' => 350,
                    'confirmed_portions' => 350,
                    'courier_id' => $fajar?->id,
                    'courier_name' => 'Pak Gunawan',
                    'vehicle_plate' => 'BG 8123 KL',
                    'confirmed_by' => $rina?->id,
                    'prepared_at' => Carbon::parse($h['date'].' 06:15:00', 'Asia/Jakarta'),
                    'departed_at' => Carbon::parse($h['date'].' 07:35:00', 'Asia/Jakarta'),
                    'arrived_at' => Carbon::parse($h['date'].' '.$h['arr'].':00', 'Asia/Jakarta'),
                    'confirmed_at' => Carbon::parse($h['date'].' '.$h['arr'].':00', 'Asia/Jakarta')->addMinutes(5),
                    'packaging_condition' => 'baik',
                    'organoleptic_taste' => 'normal',
                    'organoleptic_aroma' => 'normal',
                    'organoleptic_texture' => 'normal',
                    'organoleptic_color' => 'normal',
                    'organoleptic_status' => 'Layak',
                    'notes' => $h['note'],
                ]
            );
        }
    }

    private function alignComplaints($locations): void
    {
        $rina = User::where('username', 'rina.marlina')->first();
        $yuni = User::where('username', 'yuni.lestari')->first();
        $bima = User::where('username', 'bima.santoso')->first();
        $hendra = User::where('username', 'hendra.wijaya')->first();
        $fajar = User::where('username', 'fajar.nugroho')->first();
        $institution = BeneficiaryInstitution::first();

        if (! $institution) {
            return;
        }

        ComplaintTicket::query()->delete();

        $tickets = [
            [
                'ticket_number' => 'KLP-20261126-04',
                'sequence' => 4,
                'location_id' => $locations['Pesantren Al-Falah Palembang']->id,
                'reported_by' => $rina?->id,
                'category' => 'kemasan_tumpah',
                'severity' => 'kritis',
                'description' => 'Tumis buncis tumpah pada 4 ompreng saat pengantaran pagi',
                'photo_path' => 'complaints/bukti-ompreng-04.jpg',
                'status' => 'in_progress',
                'reported_at' => Carbon::parse('2026-11-26 08:45:00', 'Asia/Jakarta'),
                'first_response_at' => Carbon::parse('2026-11-26 08:50:00', 'Asia/Jakarta'),
                'resolved_by' => $bima?->id,
                'resolution_notes' => 'Menyiapkan 4 porsi pengganti',
                'resolved_at' => null,
            ],
            [
                'ticket_number' => 'KLP-20261121-02',
                'sequence' => 2,
                'location_id' => $locations['Pesantren Al-Falah Palembang']->id,
                'reported_by' => $rina?->id,
                'category' => 'keterlambatan',
                'severity' => 'kritis',
                'description' => 'Pengiriman tiba terlambat 20 menit karena hujan deras',
                'photo_path' => null,
                'status' => 'resolved',
                'reported_at' => Carbon::parse('2026-11-21 08:30:00', 'Asia/Jakarta'),
                'first_response_at' => Carbon::parse('2026-11-21 09:15:00', 'Asia/Jakarta'),
                'resolved_by' => $hendra?->id,
                'resolution_notes' => 'Menjadwalkan berangkat 15 menit lebih awal',
                'resolved_at' => Carbon::parse('2026-11-21 11:00:00', 'Asia/Jakarta'),
            ],
            [
                'ticket_number' => 'KLP-20261118-01',
                'sequence' => 1,
                'location_id' => $locations['SD Negeri 129 Palembang']->id,
                'reported_by' => $yuni?->id ?? $rina?->id,
                'category' => 'kelengkapan_alat',
                'severity' => 'rendah',
                'description' => '2 ompreng kurang sendok makan saat dibuka di kelas',
                'photo_path' => null,
                'status' => 'resolved',
                'reported_at' => Carbon::parse('2026-11-18 08:15:00', 'Asia/Jakarta'),
                'first_response_at' => Carbon::parse('2026-11-18 09:00:00', 'Asia/Jakarta'),
                'resolved_by' => $fajar?->id,
                'resolution_notes' => 'Mengirim sendok cadangan pada shift siang',
                'resolved_at' => Carbon::parse('2026-11-18 13:00:00', 'Asia/Jakarta'),
            ],
        ];

        foreach ($tickets as $t) {
            $ticket = ComplaintTicket::create(array_merge($t, [
                'beneficiary_institution_id' => $institution->id,
                'priority_notified_at' => $t['severity'] === 'kritis' ? $t['reported_at'] : null,
                'priority_recipients' => $t['severity'] === 'kritis' ? 'Asisten Lapangan (Bima Santoso), Kepala Dapur (Hendra Wijaya)' : null,
            ]));

            ComplaintHistory::create([
                'complaint_ticket_id' => $ticket->id,
                'status' => $t['status'],
                'user_id' => $t['resolved_by'],
                'notes' => $t['resolution_notes'],
                'created_at' => $t['resolved_at'] ?? $t['first_response_at'] ?? $t['reported_at'],
            ]);
        }
    }

    private function alignDataRevisions($locations): void
    {
        $nadia = User::where('username', 'nadia.putri')->first();
        $dewi = User::where('username', 'dewi.kartika')->first();
        $hendra = User::where('username', 'hendra.wijaya')->first();
        $menu26 = DailyMenu::whereDate('operational_date', '2026-11-26')->first();
        $routeSd131 = DeliveryRoute::where('location_id', $locations['SD Negeri 131 Palembang']->id)->first();

        DataRevision::query()->delete();

        if ($nadia && $menu26) {
            $rev1 = DataRevision::create([
                'revisable_type' => DailyMenu::class,
                'revisable_id' => $menu26->id,
                'field' => 'Sayur',
                'old_values' => ['Sayur' => 'Buncis'],
                'new_values' => ['Sayur' => 'Buncis dan wortel'],
                'reason' => 'Koreksi salah catat',
                'requested_by' => $nadia->id,
                'status' => 'menunggu',
                'approved_by' => null,
                'approved_at' => null,
            ]);
            $rev1->forceFill([
                'created_at' => Carbon::parse('2026-11-26 08:20:00', 'Asia/Jakarta'),
                'updated_at' => Carbon::parse('2026-11-26 08:20:00', 'Asia/Jakarta'),
            ])->saveQuietly();
        }

        if ($dewi && $hendra && $routeSd131) {
            $rev2 = DataRevision::create([
                'revisable_type' => DeliveryRoute::class,
                'revisable_id' => $routeSd131->id,
                'field' => 'Porsi diterima',
                'old_values' => ['confirmed_portions' => 210],
                'new_values' => ['confirmed_portions' => 208],
                'reason' => 'Salah input saat konfirmasi',
                'requested_by' => $dewi->id,
                'status' => 'disetujui',
                'approved_by' => $hendra->id,
                'decision_notes' => 'Disetujui sesuai laporan lapangan.',
                'approved_at' => Carbon::parse('2026-11-25 16:40:00', 'Asia/Jakarta'),
            ]);
            $rev2->forceFill([
                'created_at' => Carbon::parse('2026-11-25 16:30:00', 'Asia/Jakarta'),
                'updated_at' => Carbon::parse('2026-11-25 16:40:00', 'Asia/Jakarta'),
            ])->saveQuietly();
        }
    }

    private function alignFoodSafety(): void
    {
        $nadia = User::where('username', 'nadia.putri')->first();
        $hendra = User::where('username', 'hendra.wijaya')->first();

        $check26 = FoodSafetyCheck::whereDate('check_date', '2026-11-26')
            ->where('shift', 'pagi')
            ->first();

        if ($check26) {
            $check26->update([
                'inspector_id' => $nadia?->id,
                'checked_at' => Carbon::parse('2026-11-26 07:45:00', 'Asia/Jakarta'),
                'total_volunteers' => 35,
                'compliant_volunteers' => 35,
            ]);
        }

        FoodSafetyFinding::query()->delete();

        FoodSafetyFinding::create([
            'food_safety_check_id' => $check26?->id,
            'finding_date' => '2026-11-26',
            'type' => 'Suhu',
            'description' => 'Suhu makanan siap saji di bawah standar SOP (tercatat 56,5 °C, standar >= 60 °C)',
            'status' => 'terbuka',
            'handled_by' => null,
            'handler_note' => null,
            'handled_at' => null,
        ]);

        FoodSafetyFinding::create([
            'finding_date' => '2026-11-25',
            'type' => 'APD',
            'description' => 'Relawan tanpa penutup rambut pada shift siang',
            'status' => 'ditindaklanjuti',
            'handled_by' => $hendra?->id,
            'handler_note' => 'Sudah diberikan penutup rambut cadangan dan briefing ulang.',
            'handled_at' => Carbon::parse('2026-11-25 14:30:00', 'Asia/Jakarta'),
        ]);
    }

    private function alignMenu27Procurement(): void
    {
        $menu27 = DailyMenu::whereDate('operational_date', '2026-11-27')->first();
        $nadia = User::where('username', 'nadia.putri')->first();

        if (! $menu27 || ! $nadia) {
            return;
        }

        $ingredients = Ingredient::query()->pluck('id', 'name');
        $specs = [
            ['Beras', 140, 'CV Pangan Nusantara'],
            ['Ikan Patin', 70, 'UD Ikan Sungai Musi'],
            ['Bayam', 35, 'Tani Makmur Sumsel'],
            ['Tempe', 55, null],
            ['Susu UHT', 280, null],
            ['Minyak Goreng', 10, 'CV Pangan Nusantara'],
        ];

        foreach ($specs as [$name, $qty, $supplier]) {
            $ingId = $ingredients[$name] ?? null;
            if (! $ingId) {
                continue;
            }

            InventoryTransaction::updateOrCreate(
                [
                    'daily_menu_id' => $menu27->id,
                    'ingredient_id' => $ingId,
                    'type' => 'inbound_purchase',
                ],
                [
                    'reference_no' => 'PO-20261127-'.$ingId,
                    'planned_qty' => $qty,
                    'quantity' => 0,
                    'unit_price' => 0,
                    'total_cost' => 0,
                    'supplier_name' => $supplier,
                    'verification_status' => 'menunggu',
                    'procurement_status' => 'menunggu_terima',
                    'recorded_by' => $nadia->id,
                    'notes' => 'Jadwal kedatangan malam ini 20:00 s.d. 21:00 WIB.',
                ]
            );
        }
    }
}
