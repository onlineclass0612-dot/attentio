<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Presensi Kehadiran</title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 11px;
            color: #333333;
            margin: 0;
            padding: 10px;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #2563eb;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }
        .header h1 {
            font-size: 18px;
            color: #0f172a;
            margin: 0 0 4px 0;
            text-transform: uppercase;
        }
        .header p {
            margin: 0;
            color: #64748b;
            font-size: 10px;
        }
        .meta-table {
            width: 100%;
            margin-bottom: 15px;
        }
        .meta-table td {
            font-size: 10px;
            padding: 2px 0;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 5px;
        }
        .data-table th, .data-table td {
            border: 1px solid #cbd5e1;
            padding: 6px 8px;
            text-align: left;
        }
        .data-table th {
            background-color: #2563eb;
            color: #ffffff;
            font-weight: bold;
            font-size: 10px;
            text-transform: uppercase;
        }
        .data-table tr:nth-child(even) {
            background-color: #f8fafc;
        }
        .badge {
            display: inline-block;
            padding: 2px 6px;
            font-size: 9px;
            font-weight: bold;
            border-radius: 4px;
            text-transform: uppercase;
        }
        .badge-present { background-color: #dcfce7; color: #15803d; }
        .badge-late { background-color: #fef3c7; color: #b45309; }
        .badge-leave { background-color: #dbeafe; color: #1d4ed8; }
        .badge-sick { background-color: #f3e8ff; color: #7e22ce; }
        .badge-absent { background-color: #ffe4e6; color: #be123c; }
        .footer {
            margin-top: 30px;
            font-size: 9px;
            color: #94a3b8;
            text-align: right;
        }
    </style>
</head>
<body>

    <div class="header">
        <h1>PT Attention Inovasi Digital</h1>
        <p>Laporan Rekapitulasi Presensi & Kehadiran Karyawan</p>
    </div>

    <table class="meta-table">
        <tr>
            <td style="width: 15%;"><strong>Periode:</strong></td>
            <td style="width: 35%;">{{ Carbon\Carbon::parse($startDate)->format('d/m/Y') }} s/d {{ Carbon\Carbon::parse($endDate)->format('d/m/Y') }}</td>
            <td style="width: 15%;"><strong>Departemen:</strong></td>
            <td style="width: 35%;">{{ $department->name ?? 'Semua Departemen' }}</td>
        </tr>
        <tr>
            <td><strong>Lokasi Kantor:</strong></td>
            <td>{{ $branch->name ?? 'Semua Lokasi' }}</td>
            <td><strong>Tanggal Cetak:</strong></td>
            <td>{{ Carbon\Carbon::now()->translatedFormat('d F Y, H:i') }} WIB</td>
        </tr>
    </table>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 8%;">Tanggal</th>
                <th style="width: 10%;">NIK</th>
                <th style="width: 16%;">Nama Karyawan</th>
                <th style="width: 12%;">Departemen</th>
                <th style="width: 7%;">Masuk</th>
                <th style="width: 7%;">Pulang</th>
                <th style="width: 9%;">Terlambat</th>
                <th style="width: 9%;">Pulang Awal</th>
                <th style="width: 10%;">Durasi Kerja</th>
                <th style="width: 12%;">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($attendances as $att)
                <tr>
                    <td>{{ $att->date ? $att->date->format('d/m/Y') : '-' }}</td>
                    <td><strong>{{ $att->employee?->nik ?? '-' }}</strong></td>
                    <td>{{ $att->employee?->user?->name ?? '-' }}</td>
                    <td>{{ $att->employee?->department?->name ?? '-' }}</td>
                    <td>{{ $att->clock_in ? Carbon\Carbon::parse($att->clock_in)->format('H:i') : '--:--' }}</td>
                    <td>{{ $att->clock_out ? Carbon\Carbon::parse($att->clock_out)->format('H:i') : '--:--' }}</td>
                    <td>{{ $att->late_minutes > 0 ? $att->late_minutes . ' mnt' : '-' }}</td>
                    <td>{{ $att->early_leave_minutes > 0 ? $att->early_leave_minutes . ' mnt' : '-' }}</td>
                    <td>{{ $att->work_duration_minutes > 0 ? floor($att->work_duration_minutes / 60) . 'j ' . ($att->work_duration_minutes % 60) . 'm' : '-' }}</td>
                    <td>
                        <span class="badge badge-{{ $att->status }}">{{ strtoupper($att->status) }}</span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" style="text-align: center; color: #94a3b8; padding: 20px;">Tidak ada data presensi untuk periode ini.</td>
                </tr>
            @endforelse
        </tbody>
        @if($attendances->isNotEmpty())
        <tfoot>
            <tr style="background-color: #f1f5f9; font-weight: bold;">
                <td colspan="6" style="text-align: right; font-weight: bold;">TOTAL KESELURUHAN:</td>
                <td style="color: #b45309; font-weight: bold;">{{ $totalLateMinutes ?? 0 }} mnt</td>
                <td style="color: #be123c; font-weight: bold;">{{ $totalEarlyLeaveMinutes ?? 0 }} mnt</td>
                <td style="font-weight: bold;">
                    {{ isset($totalWorkDurationMinutes) && $totalWorkDurationMinutes > 0 ? floor($totalWorkDurationMinutes / 60) . 'j ' . ($totalWorkDurationMinutes % 60) . 'm' : '0j 0m' }}
                </td>
                <td><strong>{{ $attendances->count() }} Data</strong></td>
            </tr>
        </tfoot>
        @endif
    </table>

    <!-- Ringkasan Eksekutif Payroll -->
    <div style="margin-top: 15px; border: 1px solid #cbd5e1; border-radius: 6px; padding: 10px; background-color: #f8fafc;">
        <p style="margin: 0 0 6px 0; font-size: 10px; font-weight: bold; color: #0f172a; text-transform: uppercase;">
            📊 Rekapitulasi Metrik Kehadiran & Penggajian (Payroll Summary)
        </p>
        <table style="width: 100%; font-size: 9px;">
            <tr>
                <td style="width: 25%;">Total Hadir Tepat Waktu: <strong>{{ $totalPresent ?? 0 }} Hari</strong></td>
                <td style="width: 25%;">Total Terlambat: <strong>{{ $totalLate ?? 0 }} Kali</strong></td>
                <td style="width: 25%;">Total Cuti & Izin: <strong>{{ $totalLeave ?? 0 }} Hari</strong></td>
                <td style="width: 25%;">Total Log Presensi: <strong>{{ $attendances->count() }} Record</strong></td>
            </tr>
            <tr>
                <td style="padding-top: 4px;">Akumulasi Keterlambatan: <strong style="color: #b45309;">{{ $totalLateMinutes ?? 0 }} Menit</strong></td>
                <td style="padding-top: 4px;">Akumulasi Pulang Awal: <strong style="color: #be123c;">{{ $totalEarlyLeaveMinutes ?? 0 }} Menit</strong></td>
                <td style="padding-top: 4px;" colspan="2">Total Jam Kerja Efektif: <strong>{{ isset($totalWorkDurationMinutes) && $totalWorkDurationMinutes > 0 ? floor($totalWorkDurationMinutes / 60) . ' Jam ' . ($totalWorkDurationMinutes % 60) . ' Menit' : '0 Jam' }}</strong></td>
            </tr>
        </table>
    </div>

    <div class="footer">
        <p>Dicetak otomatis melalui Attention OS Workforce Management &bull; Laporan Resmi</p>
    </div>

</body>
</html>
