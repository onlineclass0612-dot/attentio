<?php

namespace App\Http\Controllers\Admin;

use App\Exports\AttendanceReportExport;
use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Branch;
use App\Models\Department;
use App\Models\Employee;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $isSupervisorOnly = $user->hasRole('Supervisor') && !$user->hasAnyRole(['Super Admin', 'HR Manager']);
        $spvDepartmentId = $isSupervisorOnly ? $user->employee?->department_id : null;

        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->format('Y-m-d'));
        $departmentId = $request->input('department_id');
        $branchId = $request->input('branch_id');

        $query = Attendance::with(['employee.user', 'employee.department', 'employee.position', 'branch', 'shift'])
            ->whereDate('date', '>=', $startDate)
            ->whereDate('date', '<=', $endDate);

        if ($spvDepartmentId) {
            $query->whereHas('employee', fn($q) => $q->where('department_id', $spvDepartmentId));
        } elseif ($departmentId) {
            $query->whereHas('employee', fn($q) => $q->where('department_id', $departmentId));
        }

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        $attendances = $query->orderBy('date', 'desc')->paginate(25);

        // Agregasi Ringkasan
        $allRecords = (clone $query)->get();
        $totalPresent = $allRecords->where('status', 'present')->count();
        $totalLate = $allRecords->where('status', 'late')->count();
        $totalLeave = $allRecords->whereIn('status', ['leave', 'sick', 'permission'])->count();
        $totalLateMinutes = $allRecords->sum('late_minutes');
        $totalEarlyLeaveMinutes = $allRecords->sum('early_leave_minutes');

        $departments = $spvDepartmentId ? Department::where('id', $spvDepartmentId)->get() : Department::all();
        $branches = Branch::all();
        $supervisorDepartment = $spvDepartmentId ? $user->employee?->department : null;

        return view('admin.reports.index', compact(
            'attendances',
            'departments',
            'branches',
            'startDate',
            'endDate',
            'departmentId',
            'branchId',
            'totalPresent',
            'totalLate',
            'totalLeave',
            'totalLateMinutes',
            'totalEarlyLeaveMinutes',
            'supervisorDepartment'
        ));
    }

    public function exportExcel(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->format('Y-m-d'));
        $departmentId = $request->input('department_id');
        $branchId = $request->input('branch_id');

        $fileName = "Rekap_Presensi_{$startDate}_sampai_{$endDate}.xlsx";

        return Excel::download(
            new AttendanceReportExport($startDate, $endDate, $departmentId, $branchId),
            $fileName
        );
    }

    public function exportPdf(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->format('Y-m-d'));
        $departmentId = $request->input('department_id');
        $branchId = $request->input('branch_id');

        $query = Attendance::with(['employee.user', 'employee.department', 'employee.position', 'branch', 'shift'])
            ->whereDate('date', '>=', $startDate)
            ->whereDate('date', '<=', $endDate);

        if ($departmentId) {
            $query->whereHas('employee', fn($q) => $q->where('department_id', $departmentId));
        }

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        $attendances = $query->orderBy('date', 'desc')->get();
        $totalLateMinutes = $attendances->sum('late_minutes');
        $totalEarlyLeaveMinutes = $attendances->sum('early_leave_minutes');
        $totalWorkDurationMinutes = $attendances->sum('work_duration_minutes');
        $totalPresent = $attendances->where('status', 'present')->count();
        $totalLate = $attendances->where('status', 'late')->count();
        $totalLeave = $attendances->whereIn('status', ['leave', 'sick', 'permission'])->count();

        $department = $departmentId ? Department::find($departmentId) : null;
        $branch = $branchId ? Branch::find($branchId) : null;

        $pdf = Pdf::loadView('admin.reports.pdf', compact(
            'attendances',
            'startDate',
            'endDate',
            'department',
            'branch',
            'totalLateMinutes',
            'totalEarlyLeaveMinutes',
            'totalWorkDurationMinutes',
            'totalPresent',
            'totalLate',
            'totalLeave'
        ))->setPaper('a4', 'landscape');

        return $pdf->download("Laporan_Presensi_{$startDate}_sampai_{$endDate}.pdf");
    }
}
