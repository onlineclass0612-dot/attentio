<?php

namespace App\Exports;

use App\Models\Attendance;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AttendanceReportExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize, WithTitle
{
    public function __construct(
        protected $startDate,
        protected $endDate,
        protected $departmentId = null,
        protected $branchId = null
    ) {}

    public function collection(): Collection
    {
        $query = Attendance::with(['employee.user', 'employee.department', 'employee.position', 'branch', 'shift'])
            ->whereDate('date', '>=', $this->startDate)
            ->whereDate('date', '<=', $this->endDate);

        if ($this->departmentId) {
            $query->whereHas('employee', function ($q) {
                $q->where('department_id', $this->departmentId);
            });
        }

        if ($this->branchId) {
            $query->where('branch_id', $this->branchId);
        }

        return $query->orderBy('date', 'desc')->get();
    }

    public function headings(): array
    {
        return [
            'Tanggal',
            'NIK',
            'Nama Karyawan',
            'Departemen',
            'Jabatan',
            'Lokasi Kantor',
            'Jam Masuk',
            'Jam Keluar',
            'Status',
            'Terlambat (Menit)',
            'Pulang Awal (Menit)',
            'Durasi Kerja (Menit)',
            'Catatan',
        ];
    }

    public function map(mixed $row): array
    {
        return [
            $row->date ? $row->date->format('d/m/Y') : '-',
            $row->employee?->nik ?? '-',
            $row->employee?->user?->name ?? '-',
            $row->employee?->department?->name ?? '-',
            $row->employee?->position?->name ?? '-',
            $row->branch?->name ?? '-',
            $row->clock_in ?? '-',
            $row->clock_out ?? '-',
            strtoupper((string) $row->status),
            $row->late_minutes ?? 0,
            $row->early_leave_minutes ?? 0,
            $row->work_duration_minutes ?? 0,
            $row->notes ?? '-',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FF2563EB'], // Primary blue
                ],
            ],
        ];
    }

    public function title(): string
    {
        return 'Laporan Presensi';
    }
}

