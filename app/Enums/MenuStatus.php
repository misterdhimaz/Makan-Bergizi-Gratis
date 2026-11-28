<?php

namespace App\Enums;

enum MenuStatus: string
{
    case DRAF = 'draf';
    case MENUNGGU_PEMERIKSAAN_ANGGARAN = 'menunggu_pemeriksaan_anggaran';
    case LOLOS_PAGU = 'lolos_pagu';
    case PERLU_REVISI_ANGGARAN = 'perlu_revisi_anggaran';
    case DISETUJUI = 'disetujui';
    case MINTA_REVISI = 'minta_revisi';

    /** Status operasional warisan di luar alur perencanaan menu. */
    case IN_PRODUCTION = 'in_production';
    case COMPLETED = 'completed';

    public function label(): string
    {
        return match ($this) {
            self::DRAF => 'Draf',
            self::MENUNGGU_PEMERIKSAAN_ANGGARAN => 'Menunggu Pemeriksaan Anggaran',
            self::LOLOS_PAGU => 'Lolos PAGU',
            self::PERLU_REVISI_ANGGARAN => 'Perlu Revisi Anggaran',
            self::DISETUJUI => 'Disetujui (terkunci)',
            self::MINTA_REVISI => 'Minta Revisi',
            self::IN_PRODUCTION => 'Dalam Produksi',
            self::COMPLETED => 'Selesai',
        };
    }

    public function displayLabel(): string
    {
        return match ($this) {
            self::DRAF => 'Belum diajukan',
            default => $this->label(),
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::DRAF => 'bg-surface-subtle border border-border-soft text-ink-muted',
            self::MENUNGGU_PEMERIKSAAN_ANGGARAN => 'bg-alert-soft text-on-alert-soft',
            self::LOLOS_PAGU => 'bg-success-soft text-on-success-soft',
            self::PERLU_REVISI_ANGGARAN => 'bg-danger-soft text-on-danger-soft',
            self::DISETUJUI => 'bg-success-soft text-on-success-soft',
            self::MINTA_REVISI => 'bg-alert-soft text-on-alert-soft',
            self::IN_PRODUCTION, self::COMPLETED => 'bg-surface-subtle border border-border-soft text-ink-muted',
        };
    }

    /**
     * Alias warisan Ahli Gizi → status kanonis.
     */
    public static function fromLegacy(string $status): self
    {
        return match ($status) {
            'draft', 'draf' => self::DRAF,
            'pending_budget_review', 'menunggu_pemeriksaan_anggaran' => self::MENUNGGU_PEMERIKSAAN_ANGGARAN,
            'pending_approval', 'lolos_pagu' => self::LOLOS_PAGU,
            'budget_revision', 'perlu_revisi_anggaran' => self::PERLU_REVISI_ANGGARAN,
            'approved', 'disetujui' => self::DISETUJUI,
            'revision_needed', 'minta_revisi' => self::MINTA_REVISI,
            'in_production' => self::IN_PRODUCTION,
            'completed' => self::COMPLETED,
            default => self::tryFrom($status) ?? self::DRAF,
        };
    }

    /**
     * @return list<string>
     */
    public static function editableByAhliGizi(): array
    {
        return [
            self::DRAF->value,
            self::PERLU_REVISI_ANGGARAN->value,
            self::MINTA_REVISI->value,
        ];
    }

    /**
     * @return list<string>
     */
    public static function locked(): array
    {
        return [
            self::DISETUJUI->value,
            self::IN_PRODUCTION->value,
            self::COMPLETED->value,
        ];
    }
}
