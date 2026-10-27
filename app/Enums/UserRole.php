<?php

namespace App\Enums;

enum UserRole: string
{
    case ADMIN = 'admin';
    case AHLI_GIZI = 'ahli_gizi';
    case AKUNTAN = 'akuntan';
    case KEPALA_DAPUR = 'kepala_dapur';
    case ASISTEN_LAPANGAN = 'asisten_lapangan';
    case TIM_DISTRIBUSI = 'tim_distribusi';
    case GURU_PIC = 'guru_pic';

    public function label(): string
    {
        return match ($this) {
            self::ADMIN => 'Administrator Sistem',
            self::AHLI_GIZI => 'Ahli Gizi',
            self::AKUNTAN => 'Akuntan SPPG',
            self::KEPALA_DAPUR => 'Kepala Dapur',
            self::ASISTEN_LAPANGAN => 'Asisten Lapangan',
            self::TIM_DISTRIBUSI => 'Tim Distribusi',
            self::GURU_PIC => 'Guru PIC / Posyandu',
        };
    }

    public function shortDesc(): string
    {
        return match ($this) {
            self::ADMIN => 'Otoritas penuh sistem, RBAC 7 role & mitra sekolah',
            self::AHLI_GIZI => 'Penyusunan siklus menu, kalkulasi AKG & inspeksi QC suhu',
            self::AKUNTAN => 'Verifikasi faktur belanja bahan baku & audit anggaran porsi',
            self::KEPALA_DAPUR => 'Approval rencana menu, komando masak & kontrol porsi',
            self::ASISTEN_LAPANGAN => 'Penerimaan bahan segar gudang & log pemakaian aktual',
            self::TIM_DISTRIBUSI => 'Armada pengiriman, surat jalan & bukti foto serah terima',
            self::GURU_PIC => 'Konfirmasi porsi sekolah & uji organoleptik sebelum konsumsi',
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::ADMIN => 'bg-indigo-100 text-indigo-700 border-indigo-200 ring-indigo-600/20',
            self::AHLI_GIZI => 'bg-emerald-100 text-emerald-700 border-emerald-200 ring-emerald-600/20',
            self::AKUNTAN => 'bg-sky-100 text-sky-700 border-sky-200 ring-sky-600/20',
            self::KEPALA_DAPUR => 'bg-amber-100 text-amber-700 border-amber-200 ring-amber-600/20',
            self::ASISTEN_LAPANGAN => 'bg-teal-100 text-teal-700 border-teal-200 ring-teal-600/20',
            self::TIM_DISTRIBUSI => 'bg-orange-100 text-orange-700 border-orange-200 ring-orange-600/20',
            self::GURU_PIC => 'bg-purple-100 text-purple-700 border-purple-200 ring-purple-600/20',
        };
    }

    public function badgeColor(): string
    {
        return $this->badgeClasses();
    }
}

