<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Operasional Harian — REKAGIZI MBG</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #1f2937; }
        h1 { font-size: 16px; margin-bottom: 4px; }
        p.meta { font-size: 10px; color: #4b5563; margin-top: 0; margin-bottom: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #d1d5db; padding: 6px 8px; text-align: left; }
        th { background-color: #f3f4f6; font-weight: bold; }
    </style>
</head>
<body>
    <h1>Laporan Operasional Harian — SPPG Sungai Selayur 1</h1>
    <p class="meta">Periode: {{ $report['period_label'] }} · Dicetak oleh: {{ $generatedBy }}</p>

    <table>
        <thead>
            <tr>
                <th>No. Pengiriman</th>
                <th>Tanggal</th>
                <th>Lokasi Penerima</th>
                <th>Status</th>
                <th>Dikirim</th>
                <th>Diterima</th>
                <th>Selisih</th>
                <th>Pengantar / Armada</th>
            </tr>
        </thead>
        <tbody>
            @foreach($report['routes'] as $route)
                <tr>
                    <td>{{ $route->waybill_number }}</td>
                    <td>{{ $route->operational_date?->format('Y-m-d') }}</td>
                    <td>{{ $route->location?->name }}</td>
                    <td>{{ $route->status->label() }}</td>
                    <td>{{ number_format($route->dispatched_portions, 0, ',', '.') }}</td>
                    <td>{{ $route->confirmed_portions !== null ? number_format($route->confirmed_portions, 0, ',', '.') : '-' }}</td>
                    <td>{{ $route->confirmed_portions !== null ? ($route->confirmed_portions - $route->dispatched_portions) : '-' }}</td>
                    <td>{{ $route->courier_name ?? '-' }} ({{ $route->vehicle_plate ?? '-' }})</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
