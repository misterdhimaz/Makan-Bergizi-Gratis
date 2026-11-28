<?php

namespace Database\Seeders;

use App\Enums\DeliveryStatus;
use App\Models\BeneficiaryInstitution;
use App\Models\ComplaintTicket;
use App\Models\DeliveryRoute;
use App\Models\Location;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Database\Seeder;

class GuruPicSeeder extends Seeder
{
    public function run(): void
    {
        AuditLogService::$enabled = false;

        try {
            $now = now('Asia/Jakarta');

            $pesantren = Location::where('name', 'Pesantren Al-Falah Palembang')->first();
            if (! $pesantren) {
                return;
            }

            // Pastikan target porsi Pesantren Al-Falah = 350 porsi sesuai docs/data-canonical.md
            $pesantren->update([
                'target_portions' => 350,
            ]);

            $rina = User::where('username', 'rina.marlina')->first();
            if ($rina) {
                $rina->forceFill([
                    'location_id' => $pesantren->id,
                ])->saveQuietly();
            }

            $bima = User::where('username', 'bima.santoso')->first();
            $hendra = User::where('username', 'hendra.wijaya')->first();
            $dedi = User::where('username', 'dedi.pratama')->first();
            $reporter = $rina ?? User::where('location_id', $pesantren->id)->first();
            $institution = BeneficiaryInstitution::where('name', 'like', '%Al-Falah%')->first()
                ?? BeneficiaryInstitution::first();

            // 5 hari riwayat penerimaan Pesantren Al-Falah
            $routesSpec = [
                [
                    'offset' => 0,
                    'waybill' => '#DST-'.$now->format('Ymd').'-07',
                    'status' => DeliveryStatus::BERMASALAH,
                    'dispatched' => 250,
                    'confirmed' => 246,
                    'prepared_time' => '06:15',
                    'departed_time' => '07:35',
                    'arrived_time' => '08:12',
                    'confirmed_time' => '08:20',
                    'packaging' => 'sebagian-tumpah',
                    'organoleptic' => 'Layak',
                    'notes' => 'Tumis buncis tumpah pada 4 ompreng saat tiba',
                ],
                [
                    'offset' => -1,
                    'waybill' => '#DST-'.$now->copy()->subDay()->format('Ymd').'-07',
                    'status' => DeliveryStatus::DITERIMA,
                    'dispatched' => 250,
                    'confirmed' => 250,
                    'prepared_time' => '06:10',
                    'departed_time' => '07:20',
                    'arrived_time' => '07:55',
                    'confirmed_time' => '08:02',
                    'packaging' => 'baik',
                    'organoleptic' => 'Layak',
                    'notes' => 'Diterima lengkap dalam kondisi baik.',
                ],
                [
                    'offset' => -2,
                    'waybill' => '#DST-'.$now->copy()->subDays(2)->format('Ymd').'-07',
                    'status' => DeliveryStatus::DITERIMA,
                    'dispatched' => 250,
                    'confirmed' => 250,
                    'prepared_time' => '06:10',
                    'departed_time' => '07:18',
                    'arrived_time' => '07:50',
                    'confirmed_time' => '07:58',
                    'packaging' => 'baik',
                    'organoleptic' => 'Layak',
                    'notes' => 'Sesuai pengiriman dan tepat waktu.',
                ],
                [
                    'offset' => -5,
                    'waybill' => '#DST-'.$now->copy()->subDays(5)->format('Ymd').'-07',
                    'status' => DeliveryStatus::DITERIMA,
                    'dispatched' => 250,
                    'confirmed' => 250,
                    'prepared_time' => '06:15',
                    'departed_time' => '07:45',
                    'arrived_time' => '08:20',
                    'confirmed_time' => '08:28',
                    'packaging' => 'baik',
                    'organoleptic' => 'Layak',
                    'notes' => 'Sempat terlambat karena hujan deras, seluruh porsi layak konsumsi.',
                ],
                [
                    'offset' => -6,
                    'waybill' => '#DST-'.$now->copy()->subDays(6)->format('Ymd').'-07',
                    'status' => DeliveryStatus::DITERIMA,
                    'dispatched' => 250,
                    'confirmed' => 250,
                    'prepared_time' => '06:10',
                    'departed_time' => '07:22',
                    'arrived_time' => '07:58',
                    'confirmed_time' => '08:05',
                    'packaging' => 'baik',
                    'organoleptic' => 'Layak',
                    'notes' => 'Sesuai pengiriman dan tepat waktu.',
                ],
            ];

            // Hapus rute lama SJ/SS1/007 hari ini untuk Pesantren Al-Falah agar tidak duplikat di dashboard sekolah
            DeliveryRoute::where('location_id', $pesantren->id)
                ->where('waybill_number', 'SJ/SS1/007')
                ->whereDate('operational_date', $now->toDateString())
                ->delete();

            foreach ($routesSpec as $spec) {
                $date = $now->copy()->addDays($spec['offset']);
                $dateStr = $date->toDateString();

                DeliveryRoute::updateOrCreate(
                    ['waybill_number' => $spec['waybill']],
                    [
                        'operational_date' => $dateStr,
                        'scheduled_arrival_time' => '08:00',
                        'location_id' => $pesantren->id,
                        'status' => $spec['status'],
                        'dispatched_portions' => $spec['dispatched'],
                        'confirmed_portions' => $spec['confirmed'],
                        'courier_id' => $dedi?->id,
                        'courier_name' => $dedi?->name ?? 'Dedi Pratama',
                        'vehicle_plate' => 'B 9142 KDA',
                        'confirmed_by' => $reporter?->id,
                        'prepared_at' => $date->copy()->setTimeFromTimeString($spec['prepared_time'].':00'),
                        'departed_at' => $date->copy()->setTimeFromTimeString($spec['departed_time'].':00'),
                        'arrived_at' => $date->copy()->setTimeFromTimeString($spec['arrived_time'].':00'),
                        'confirmed_at' => $date->copy()->setTimeFromTimeString($spec['confirmed_time'].':00'),
                        'notes' => $spec['notes'],
                        'packaging_condition' => $spec['packaging'],
                        'organoleptic_taste' => 'normal',
                        'organoleptic_aroma' => 'normal',
                        'organoleptic_texture' => 'normal',
                        'organoleptic_color' => 'normal',
                        'organoleptic_status' => $spec['organoleptic'],
                    ]
                );
            }

            if ($reporter && $institution) {
                $todayCode = $now->format('Ymd');

                // Konsolidasikan tiket aktif hari ini agar tidak duplikat dengan tiket TKT-* dari AhliGiziSeeder
                ComplaintTicket::where('location_id', $pesantren->id)
                    ->where('ticket_number', 'like', 'TKT-%')
                    ->delete();

                ComplaintTicket::updateOrCreate(
                    ['ticket_number' => "#KLP-{$todayCode}-04"],
                    [
                        'beneficiary_institution_id' => $institution->id,
                        'location_id' => $pesantren->id,
                        'reported_by' => $reporter->id,
                        'category' => 'kemasan_tumpah',
                        'severity' => 'kritis',
                        'description' => 'Laporan Guru PIC: tumis buncis tumpah pada 4 ompreng saat pengantaran pagi.',
                        'photo_path' => 'complaints/bukti-ompreng-04.jpg',
                        'status' => 'in_progress',
                        'reported_at' => $now->copy()->setTime(8, 45, 0),
                        'priority_notified_at' => $now->copy()->setTime(8, 45, 0),
                        'priority_recipients' => 'Asisten Lapangan (Bima Santoso), Kepala Dapur (Hendra Wijaya)',
                        'resolved_by' => $bima?->id,
                        'resolution_notes' => 'Asisten Lapangan (Bima Santoso) menyiapkan 4 porsi pengganti',
                    ]
                );

                ComplaintTicket::updateOrCreate(
                    ['ticket_number' => '#KLP-'.$now->copy()->subDays(5)->format('Ymd').'-02'],
                    [
                        'beneficiary_institution_id' => $institution->id,
                        'location_id' => $pesantren->id,
                        'reported_by' => $reporter->id,
                        'category' => 'keterlambatan',
                        'severity' => 'sedang',
                        'description' => 'Pengiriman tiba terlambat 20 menit karena hujan deras.',
                        'status' => 'resolved',
                        'reported_at' => $now->copy()->subDays(5)->setTime(8, 30, 0),
                        'resolved_by' => $hendra?->id,
                        'resolution_notes' => 'Kepala Dapur (Hendra Wijaya) menjadwalkan berangkat 15 mnt lebih awal',
                        'resolved_at' => $now->copy()->subDays(5)->setTime(9, 10, 0),
                    ]
                );

                ComplaintTicket::updateOrCreate(
                    ['ticket_number' => '#KLP-'.$now->copy()->subDays(8)->format('Ymd').'-01'],
                    [
                        'beneficiary_institution_id' => $institution->id,
                        'location_id' => $pesantren->id,
                        'reported_by' => $reporter->id,
                        'category' => 'kelengkapan_alat',
                        'severity' => 'rendah',
                        'description' => '2 ompreng kurang sendok makan saat dibuka di kelas.',
                        'status' => 'resolved',
                        'reported_at' => $now->copy()->subDays(8)->setTime(8, 15, 0),
                        'resolved_by' => $dedi?->id,
                        'resolution_notes' => 'Tim Distribusi mengirim sendok cadangan pada shift siang',
                        'resolved_at' => $now->copy()->subDays(8)->setTime(11, 20, 0),
                    ]
                );
            }
        } finally {
            AuditLogService::$enabled = true;
        }
    }
}
