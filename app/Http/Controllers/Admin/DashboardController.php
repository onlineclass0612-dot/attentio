<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\AttendanceCorrection;
use App\Models\Branch;
use App\Models\Department;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\OvertimeRequest;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $today = Carbon::today()->format('Y-m-d');
        $user = auth()->user();
        $isSupervisorOnly = $user->hasRole('Supervisor') && !$user->hasAnyRole(['Super Admin', 'HR Manager']);
        $spvDepartmentId = $isSupervisorOnly ? $user->employee?->department_id : null;
        $supervisorDepartment = $isSupervisorOnly ? $user->employee?->department : null;

        // Total Counts
        $employeeQuery = Employee::where('is_active', true);
        if ($spvDepartmentId) {
            $employeeQuery->where('department_id', $spvDepartmentId);
        }
        $totalEmployees = $employeeQuery->count();

        $totalBranches = Branch::where('is_active', true)->count();
        $totalDepartments = Department::count();

        // Today's Attendance Metrics
        $attendanceQuery = Attendance::with(['employee.user', 'employee.department', 'employee.position', 'branch', 'shift'])
            ->where('date', $today);

        if ($spvDepartmentId) {
            $attendanceQuery->whereHas('employee', fn($q) => $q->where('department_id', $spvDepartmentId));
        }

        $todayAttendances = $attendanceQuery->get();

        $presentCount = $todayAttendances->where('status', 'present')->count();
        $lateCount = $todayAttendances->where('status', 'late')->count();
        $leaveCount = $todayAttendances->whereIn('status', ['leave', 'sick', 'permission'])->count();
        $checkedInTotal = $presentCount + $lateCount;
        $absentCount = max(0, $totalEmployees - ($checkedInTotal + $leaveCount));

        // Pending Approvals Count
        $leaveQuery = LeaveRequest::where('status', 'pending');
        $overtimeQuery = OvertimeRequest::where('status', 'pending');
        $correctionQuery = AttendanceCorrection::where('status', 'pending');

        if ($spvDepartmentId) {
            $leaveQuery->whereHas('employee', fn($q) => $q->where('department_id', $spvDepartmentId));
            $overtimeQuery->whereHas('employee', fn($q) => $q->where('department_id', $spvDepartmentId));
            $correctionQuery->whereHas('employee', fn($q) => $q->where('department_id', $spvDepartmentId));
        }

        $pendingLeaves = $leaveQuery->count();
        $pendingOvertimes = $overtimeQuery->count();
        $pendingCorrections = $correctionQuery->count();
        $totalPendingApprovals = $pendingLeaves + $pendingOvertimes + $pendingCorrections;

        // Recent Today Attendances for Live Feed
        $recentAttendances = $todayAttendances->sortByDesc('created_at')->take(8);

        // Branch Locations for Map
        $branches = Branch::where('is_active', true)->get();

        // Department Attendance Breakdown
        $deptQuery = Department::withCount(['employees' => function ($q) use ($spvDepartmentId) {
            $q->where('is_active', true);
            if ($spvDepartmentId) {
                $q->where('department_id', $spvDepartmentId);
            }
        }]);

        if ($spvDepartmentId) {
            $deptQuery->where('id', $spvDepartmentId);
        }

        $departments = $deptQuery->get();

        return view('admin.dashboard', compact(
            'totalEmployees',
            'totalBranches',
            'totalDepartments',
            'presentCount',
            'lateCount',
            'leaveCount',
            'absentCount',
            'totalPendingApprovals',
            'pendingLeaves',
            'pendingOvertimes',
            'pendingCorrections',
            'recentAttendances',
            'branches',
            'departments',
            'today',
            'supervisorDepartment',
            'isSupervisorOnly'
        ));
    }
}
