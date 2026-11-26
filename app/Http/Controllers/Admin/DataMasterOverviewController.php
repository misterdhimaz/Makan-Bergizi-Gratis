<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\DataMasterCompletenessService;
use Illuminate\View\View;

class DataMasterOverviewController extends Controller
{
    public function __construct(
        private readonly DataMasterCompletenessService $completenessService,
    ) {}

    /**
     * Menampilkan ringkasan kelengkapan 7 item Data Master.
     */
    public function __invoke(): View
    {
        $completeness = $this->completenessService->evaluate();

        $routeMap = [
            'lokasi' => route('admin.data-master.lokasi.index'),
            'kategori' => route('admin.data-master.kategori.index'),
            'standar_porsi' => route('admin.data-master.standar-porsi.index'),
            'bahan' => route('admin.data-master.bahan.index'),
            'pemasok' => route('admin.data-master.pemasok.index'),
            'pagu' => route('admin.data-master.pagu.index'),
            'checklist_sop' => route('admin.data-master.checklist-sop.index'),
        ];

        return view('admin.data-master.index', [
            'completeness' => $completeness,
            'routeMap' => $routeMap,
        ]);
    }
}
