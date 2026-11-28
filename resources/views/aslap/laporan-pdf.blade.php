<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Distribusi &amp; Keluhan — REKAGIZI MBG</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #1f2937; }
        h1 { font-size: 15px; margin-bottom: 4px; }
        h2 { font-size: 13px; margin-top: 14px; margin-bottom: 6px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        th, td { border: 1px solid #d1d5db; padding: 5px 7px; text-align: left; }
        th { background-color: #f3f4f6; }
    </style>
</head>
<body>
    <h1>Laporan Distribusi &amp; Keluhan — SPPG Sungai Selayur 1</h1>
    <p>Periode: {{ $report['period_label'] }} · Dicetak oleh: {{ $generatedBy }}</p>

    <h2>Ringkasan Pengiriman</h2>
    <table>
        <thead>
            <tr>
                <th>No. Pengiriman</th>
                <th>Tanggal</th>
                <th>Lokasi</th>
                <th>Status</th>
                <th>Dikirim</th>
                <th>Diterima</th>
                <th>Selisih</th>
            </tr>
        </thead>
        <tbody>
            @foreach($report['routes'] as $route)
                <tr>
                    <td>{{ $route->waybill_number }}</td>
                    <td>{{ $route->operational_date?->format('Y-m-d') }}</td>
                    <td>{{ $route->location?->name }}</td>
                    <td>{{ $route->status->label() }}</td>
                    <td>{{ $route->dispatched_portions }}</td>
                    <td>{{ $route->confirmed_portions ?? '-' }}</td>
                    <td>{{ $route->confirmed_portions !== null ? ($route->confirmed_portions - $route->dispatched_portions) : '-' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <h2>Daftar Keluhan</h2>
    <table>
        <thead>
            <tr>
                <th>Tiket</th>
                <th>Lokasi</th>
                <th>Kategori</th>
                <th>Prioritas</th>
                <th>Status</th>
                <th>Tindakan</th>
            </tr>
        </thead>
        <tbody>
            @foreach($report['complaints'] as $c)
                <tr>
                    <td>{{ $c->ticket_number }}</td>
                    <td>{{ $c->location?->name }}</td>
                    <td>{{ $c->categoryLabel() }}</td>
                    <td>{{ in_array($c->severity, ['kritis', 'serius'], true) ? 'Serius' : 'Normal' }}</td>
                    <td>{{ $c->status }}</td>
                    <td>{{ $c->resolution_notes ?? '-' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
