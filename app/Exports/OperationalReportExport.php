<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class OperationalReportExport implements FromCollection, WithHeadings
{
    /**
     * @param  list<string>  $headings
     * @param  Collection<int, array<int, string|int|float|null>>  $rows
     */
    public function __construct(
        private readonly array $headings,
        private readonly Collection $rows,
    ) {}

    public function collection(): Collection
    {
        return $this->rows;
    }

    public function headings(): array
    {
        return $this->headings;
    }
}
