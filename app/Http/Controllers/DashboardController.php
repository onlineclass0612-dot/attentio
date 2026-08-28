<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Branch;
use App\Models\Employee;
use App\Models\LeaveBalance;
use App\Models\LeaveRequest;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // Redirect based on roles
        if ($user->hasRole(['Super Admin', 'HR Manager', 'Supervisor'])) {
            return redirect()->route('admin.dashboard');
        }

        return redirect()->route('employee.dashboard');
    }

    public function employeeDashboard()
    {
        $user = Auth::user();
        $employee = $user->employee;

        if (!$employee) {
            return view('employee.no-profile');
        }

        $today = Carbon::today()->format('Y-m-d');
        $attendanceToday = Attendance::where('employee_id', $employee->id)
            ->where('date', $today)
            ->first();

        // Recent 7 days attendances
        $recentAttendances = Attendance::where('employee_id', $employee->id)
            ->orderBy('date', 'desc')
            ->limit(7)
            ->get();

        // Current Year Leave Balances
        $currentYear = (int) date('Y');
        $leaveBalances = LeaveBalance::with('leaveType')
            ->where('employee_id', $employee->id)
            ->where('year', $currentYear)
            ->get();

        // Monthly Summary
        $startOfMonth = Carbon::now()->startOfMonth()->format('Y-m-d');
        $endOfMonth = Carbon::now()->endOfMonth()->format('Y-m-d');

        $monthlyPresent = Attendance::where('employee_id', $employee->id)
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->whereIn('status', ['present', 'late', 'early_leave'])
            ->count();

        $monthlyLate = Attendance::where('employee_id', $employee->id)
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->where('status', 'late')
            ->count();

        $monthlyLeave = Attendance::where('employee_id', $employee->id)
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->whereIn('status', ['leave', 'sick', 'permission'])
            ->count();

        $assignedBranch = $employee->branch ?? Branch::where('is_active', true)->first();

        return view('employee.dashboard', compact(
            'employee',
            'attendanceToday',
            'recentAttendances',
            'leaveBalances',
            'monthlyPresent',
            'monthlyLate',
            'monthlyLeave',
            'assignedBranch'
        ));
    }
}
