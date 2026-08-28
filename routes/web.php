<?php

use App\Http\Controllers\Admin\ActivityLogController as AdminActivityLogController;
use App\Http\Controllers\Admin\ApprovalController as AdminApprovalController;
use App\Http\Controllers\Admin\AttendanceController as AdminAttendanceController;
use App\Http\Controllers\Admin\BranchController as AdminBranchController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\DepartmentController as AdminDepartmentController;
use App\Http\Controllers\Admin\EmployeeController as AdminEmployeeController;
use App\Http\Controllers\Admin\PositionController as AdminPositionController;
use App\Http\Controllers\Admin\ReportController as AdminReportController;
use App\Http\Controllers\Admin\ShiftController as AdminShiftController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Employee\AttendanceController as EmployeeAttendanceController;
use App\Http\Controllers\Employee\LeaveController as EmployeeLeaveController;
use App\Http\Controllers\Employee\OvertimeController as EmployeeOvertimeController;
use App\Http\Controllers\Employee\ProfileController as EmployeeProfileController;
use Illuminate\Support\Facades\Route;

// Guest Routes
Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);

// Authenticated Routes
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // 1. Employee ESS Routes (Protected by active_employee middleware)
    Route::prefix('employee')->name('employee.')->middleware('active_employee')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'employeeDashboard'])->name('dashboard');
        
        // Attendance & Clock In/Out
        Route::get('/attendance/clock', [EmployeeAttendanceController::class, 'create'])->name('attendance.create');
        Route::post('/attendance/clock-in', [EmployeeAttendanceController::class, 'clockIn'])->name('attendance.clock-in');
        Route::post('/attendance/clock-out', [EmployeeAttendanceController::class, 'clockOut'])->name('attendance.clock-out');
        Route::get('/attendance/history', [EmployeeAttendanceController::class, 'history'])->name('attendance.history');
        Route::post('/attendance/correction', [EmployeeAttendanceController::class, 'storeCorrection'])->name('attendance.correction');
        
        // Leave / Cuti
        Route::get('/leave', [EmployeeLeaveController::class, 'index'])->name('leave.index');
        Route::post('/leave', [EmployeeLeaveController::class, 'store'])->name('leave.store');
        
        // Overtime / Lembur
        Route::get('/overtime', [EmployeeOvertimeController::class, 'index'])->name('overtime.index');
        Route::post('/overtime', [EmployeeOvertimeController::class, 'store'])->name('overtime.store');
        
        // Profile
        Route::get('/profile', [EmployeeProfileController::class, 'show'])->name('profile.show');
        Route::post('/profile', [EmployeeProfileController::class, 'update'])->name('profile.update');
    });

    // 2. Admin & HR Management Routes
    Route::prefix('admin')->name('admin.')->group(function () {
        
        // A. Shared Admin & Supervisor Routes
        Route::middleware('role:Super Admin|HR Manager|Supervisor')->group(function () {
            Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
            Route::get('/attendance', [AdminAttendanceController::class, 'index'])->name('attendance.index');
            Route::get('/reports', [AdminReportController::class, 'index'])->name('reports.index');
            Route::get('/employees', [AdminEmployeeController::class, 'index'])->name('employees.index');

            // Approval Center (Super Admin, HR Manager, Supervisor)
            Route::prefix('approvals')->name('approvals.')->group(function () {
                Route::get('/', [AdminApprovalController::class, 'index'])->name('index');
                Route::post('/leave/{leaveRequest}/approve', [AdminApprovalController::class, 'approveLeave'])->name('leave.approve');
                Route::post('/leave/{leaveRequest}/reject', [AdminApprovalController::class, 'rejectLeave'])->name('leave.reject');
                Route::post('/overtime/{overtimeRequest}/approve', [AdminApprovalController::class, 'approveOvertime'])->name('overtime.approve');
                Route::post('/overtime/{overtimeRequest}/reject', [AdminApprovalController::class, 'rejectOvertime'])->name('overtime.reject');
                Route::post('/correction/{correction}/approve', [AdminApprovalController::class, 'approveCorrection'])->name('correction.approve');
                Route::post('/correction/{correction}/reject', [AdminApprovalController::class, 'rejectCorrection'])->name('correction.reject');
            });
        });

        // B. Restricted Master Data & Operations (Super Admin & HR Manager Only)
        Route::middleware('role:Super Admin|HR Manager')->group(function () {
            Route::resource('employees', AdminEmployeeController::class)->except(['index']);
            Route::resource('attendance', AdminAttendanceController::class)->except(['index', 'destroy']);
            Route::resource('branches', AdminBranchController::class);
            Route::resource('departments', AdminDepartmentController::class);
            Route::resource('positions', AdminPositionController::class);
            Route::resource('shifts', AdminShiftController::class);

            // Reports Export
            Route::get('/reports/excel', [AdminReportController::class, 'exportExcel'])->name('reports.excel');
            Route::get('/reports/pdf', [AdminReportController::class, 'exportPdf'])->name('reports.pdf');
        });

        // C. Super Admin Exclusive Routes (Activity Logs / Audit Trail)
        Route::middleware('role:Super Admin')->group(function () {
            Route::get('/activity-logs', [AdminActivityLogController::class, 'index'])->name('activity_logs.index');
        });
    });
});

