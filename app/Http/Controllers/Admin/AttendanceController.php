<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Branch;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Shift;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $isSupervisorOnly = $user->hasRole('Supervisor') && !$user->hasAnyRole(['Super Admin', 'HR Manager']);
        $spvDepartmentId = $isSupervisorOnly ? $user->employee?->department_id : null;

        $query = Attendance::with(['employee.user', 'employee.department', 'employee.position', 'branch', 'shift']);

        // Date filter handling:
        // 1. If explicit 'date' param is passed:
        //    - If filled (e.g. ?date=2026-08-28): filter by that date
        //    - If empty (e.g. ?date=): show all dates (no date filter)
        // 2. If NO 'date' param in request:
        //    - If user is paging (?page=2) or filtering (search, department, etc.): keep date unconstrained (all dates)
        //    - ONLY if clean request with zero query parameters: default to Today ($date = Carbon::today()->format('Y-m-d'))
        if ($request->has('date')) {
            $date = $request->input('date');
            if (!empty($date)) {
                $query->where('date', $date);
            }
        } elseif (empty($request->query())) {
            $date = Carbon::today()->format('Y-m-d');
            $query->where('date', $date);
        } else {
            $date = '';
        }

        if ($spvDepartmentId) {
            $query->whereHas('employee', function ($q) use ($spvDepartmentId) {
                $q->where('department_id', $spvDepartmentId);
            });
        } elseif ($request->filled('department_id')) {
            $query->whereHas('employee', function ($q) use ($request) {
                $q->where('department_id', $request->department_id);
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('employee', function ($q) use ($search) {
                $q->where('nik', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($uq) use ($search) {
                      $uq->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $attendances = $query->orderBy('date', 'desc')
            ->orderBy('clock_in', 'asc')
            ->paginate(20)
            ->withQueryString();

        $departments = $spvDepartmentId ? Department::where('id', $spvDepartmentId)->get() : Department::all();
        $branches = Branch::all();
        $supervisorDepartment = $spvDepartmentId ? $user->employee?->department : null;

        return view('admin.attendance.index', compact('attendances', 'departments', 'branches', 'date', 'supervisorDepartment'));
    }

    public function create()
    {
        $employees = Employee::with('user')->where('is_active', true)->get();
        $branches = Branch::where('is_active', true)->get();
        $shifts = Shift::where('is_active', true)->get();

        return view('admin.attendance.create', compact('employees', 'branches', 'shifts'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'date' => 'required|date',
            'status' => 'required|in:present,late,early_leave,absent,leave,sick,permission',
            'clock_in' => 'nullable',
            'clock_out' => 'nullable',
            'branch_id' => 'nullable|exists:branches,id',
            'shift_id' => 'nullable|exists:shifts,id',
            'notes' => 'nullable|string',
        ]);

        $att = Attendance::updateOrCreate(
            ['employee_id' => $request->employee_id, 'date' => $request->date],
            [
                'branch_id' => $request->branch_id,
                'shift_id' => $request->shift_id,
                'status' => $request->status,
                'clock_in' => $request->clock_in,
                'clock_out' => $request->clock_out,
                'notes' => $request->notes,
            ]
        );

        $empName = $att->employee?->user?->name ?? 'Karyawan';
        \App\Models\ActivityLog::record(
            'Log Presensi',
            'CREATE',
            "Membuat catatan presensi manual untuk {$empName} pada tanggal {$request->date} (Status: {$request->status})",
            $att->toArray()
        );

        return redirect()->route('admin.attendance.index', ['date' => $request->date])
            ->with('success', 'Data presensi berhasil disimpan.');
    }

    public function edit(Attendance $attendance)
    {
        $attendance->load(['employee.user', 'branch', 'shift']);
        $branches = Branch::where('is_active', true)->get();
        $shifts = Shift::where('is_active', true)->get();

        return view('admin.attendance.edit', compact('attendance', 'branches', 'shifts'));
    }

    public function update(Request $request, Attendance $attendance)
    {
        $oldData = $attendance->only(['status', 'clock_in', 'clock_out', 'notes']);

        $request->validate([
            'status' => 'required|in:present,late,early_leave,absent,leave,sick,permission',
            'clock_in' => 'nullable',
            'clock_out' => 'nullable',
            'branch_id' => 'nullable|exists:branches,id',
            'shift_id' => 'nullable|exists:shifts,id',
            'late_minutes' => 'nullable|integer',
            'early_leave_minutes' => 'nullable|integer',
            'notes' => 'nullable|string',
        ]);

        $attendance->update($request->only([
            'status', 'clock_in', 'clock_out', 'branch_id', 'shift_id', 'late_minutes', 'early_leave_minutes', 'notes'
        ]));

        $empName = $attendance->employee?->user?->name ?? 'Karyawan';
        \App\Models\ActivityLog::record(
            'Log Presensi',
            'UPDATE',
            "Mengubah data presensi {$empName} untuk tanggal " . ($attendance->date ? $attendance->date->format('d/m/Y') : '-') . " (Status baru: {$attendance->status})",
            ['sebelum' => $oldData, 'sesudah' => $attendance->only(['status', 'clock_in', 'clock_out', 'notes'])]
        );

        return redirect()->route('admin.attendance.index', ['date' => $attendance->date->format('Y-m-d')])
            ->with('success', 'Data presensi berhasil diperbarui.');
    }

    public function destroy(Attendance $attendance)
    {
        return redirect()->back()->with('error', 'Kebijakan Kepatuhan Audit: Data log presensi tidak dapat dihapus demi menjaga integritas data penggajian (Payroll Integrity). Anda hanya diperkenankan melakukan penyesuaian/edit status presensi.');
    }
}
