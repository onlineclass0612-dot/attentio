<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Attendance;
use App\Models\AttendanceCorrection;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\OvertimeRequest;
use App\Services\LeaveService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ApprovalController extends Controller
{
    public function __construct(
        protected LeaveService $leaveService
    ) {}

    /**
     * Determine if current user is a Supervisor only (department scoped).
     */
    protected function isSupervisorOnly(): bool
    {
        $user = Auth::user();
        return $user && $user->hasRole('Supervisor') && !$user->hasAnyRole(['Super Admin', 'HR Manager']);
    }

    /**
     * Authorize whether the current user is permitted to process approval for the applicant.
     */
    protected function authorizeApproval(Employee $applicant): array
    {
        $user = Auth::user();
        if (!$user) {
            return ['allowed' => false, 'message' => 'Silakan login terlebih dahulu.'];
        }

        // 1. Anti Self-Approval Rule: Users cannot approve/reject their own requests
        if ($user->employee && $user->employee->id === $applicant->id) {
            return [
                'allowed' => false,
                'message' => 'Anda tidak dapat menyetujui atau menolak permohonan Anda sendiri (Self-Approval dilarang).'
            ];
        }

        // 2. Super Admin: Full authority over all company requests
        if ($user->hasRole('Super Admin')) {
            return ['allowed' => true];
        }

        // 3. HR Manager: Company-wide authority over Staff and Division Supervisors/Managers
        if ($user->hasRole('HR Manager')) {
            $applicantRoles = $applicant->user?->getRoleNames()->toArray() ?? [];
            if (in_array('Super Admin', $applicantRoles)) {
                return ['allowed' => false, 'message' => 'Permohonan Super Admin hanya dapat diproses oleh sesama Super Admin.'];
            }
            return ['allowed' => true];
        }

        // 4. Supervisor: Can only approve Staff in their assigned department
        if ($user->hasRole('Supervisor')) {
            $spvEmployee = $user->employee;
            if (!$spvEmployee || $spvEmployee->department_id !== $applicant->department_id) {
                return [
                    'allowed' => false,
                    'message' => 'Anda tidak memiliki wewenang untuk memproses permohonan karyawan di luar divisi Anda.'
                ];
            }

            // Supervisor cannot approve other Supervisors or HR Managers
            $applicantRoles = $applicant->user?->getRoleNames()->toArray() ?? [];
            if (in_array('Supervisor', $applicantRoles) || in_array('HR Manager', $applicantRoles) || in_array('Super Admin', $applicantRoles)) {
                return [
                    'allowed' => false,
                    'message' => 'Permohonan sesama pimpinan divisi atau HR hanya dapat disetujui oleh HR Manager atau Super Admin.'
                ];
            }

            return ['allowed' => true];
        }

        return ['allowed' => false, 'message' => 'Anda tidak memiliki hak akses untuk memproses persetujuan ini.'];
    }

    public function index()
    {
        $user = Auth::user();
        $userEmpId = $user->employee?->id;
        $isSupervisorOnly = $this->isSupervisorOnly();
        $spvDeptId = $isSupervisorOnly ? $user->employee?->department_id : null;

        $leaveQuery = LeaveRequest::with(['employee.user.roles', 'employee.department', 'leaveType'])
            ->where('status', 'pending');
        
        $overtimeQuery = OvertimeRequest::with(['employee.user.roles', 'employee.department'])
            ->where('status', 'pending');

        $correctionQuery = AttendanceCorrection::with(['employee.user.roles', 'employee.department', 'attendance'])
            ->where('status', 'pending');

        // 1. Never show own requests in approval queue (Self-Approval Prevention)
        if ($userEmpId) {
            $leaveQuery->where('employee_id', '!=', $userEmpId);
            $overtimeQuery->where('employee_id', '!=', $userEmpId);
            $correctionQuery->where('employee_id', '!=', $userEmpId);
        }

        // 2. Supervisor scoping: Only staff in their department
        if ($isSupervisorOnly) {
            if ($spvDeptId) {
                $scopeClosure = function ($q) use ($spvDeptId) {
                    $q->where('department_id', $spvDeptId)
                      ->whereDoesntHave('user.roles', fn($rq) => $rq->whereIn('name', ['Supervisor', 'HR Manager', 'Super Admin']));
                };

                $leaveQuery->whereHas('employee', $scopeClosure);
                $overtimeQuery->whereHas('employee', $scopeClosure);
                $correctionQuery->whereHas('employee', $scopeClosure);
            } else {
                $leaveQuery->whereRaw('1 = 0');
                $overtimeQuery->whereRaw('1 = 0');
                $correctionQuery->whereRaw('1 = 0');
            }
        }

        $leaves = $leaveQuery->latest()->get();
        $overtimes = $overtimeQuery->latest()->get();
        $corrections = $correctionQuery->latest()->get();

        $supervisorDepartment = $spvDeptId ? $user->employee?->department : null;

        return view('admin.approvals.index', compact('leaves', 'overtimes', 'corrections', 'supervisorDepartment'));
    }

    public function approveLeave(LeaveRequest $leaveRequest)
    {
        $auth = $this->authorizeApproval($leaveRequest->employee);
        if (!$auth['allowed']) {
            return redirect()->back()->with('error', $auth['message']);
        }

        $empName = $leaveRequest->employee?->user?->name ?? 'Karyawan';
        $leaveTypeName = $leaveRequest->leaveType?->name ?? 'Cuti';
        $result = $this->leaveService->approveRequest($leaveRequest, Auth::user());

        if (!$result['success']) {
            return redirect()->back()->with('error', $result['message']);
        }

        ActivityLog::record(
            'Persetujuan Cuti',
            'APPROVE',
            "Menyetujui permohonan {$leaveTypeName} untuk {$empName} ({$leaveRequest->total_days} hari)",
            [
                'deskripsi' => "Permohonan {$leaveTypeName} selama {$leaveRequest->total_days} hari ({$leaveRequest->start_date->format('d/m/Y')} s/d {$leaveRequest->end_date->format('d/m/Y')}) disetujui oleh pimpinan."
            ]
        );

        return redirect()->back()->with('success', $result['message']);
    }

    public function rejectLeave(Request $request, LeaveRequest $leaveRequest)
    {
        $auth = $this->authorizeApproval($leaveRequest->employee);
        if (!$auth['allowed']) {
            return redirect()->back()->with('error', $auth['message']);
        }

        $request->validate(['rejection_reason' => 'required|string']);
        $empName = $leaveRequest->employee?->user?->name ?? 'Karyawan';
        $leaveTypeName = $leaveRequest->leaveType?->name ?? 'Cuti';

        $result = $this->leaveService->rejectRequest($leaveRequest, Auth::user(), $request->rejection_reason);

        ActivityLog::record(
            'Persetujuan Cuti',
            'REJECT',
            "Menolak permohonan {$leaveTypeName} untuk {$empName}",
            [
                'alasan_penolakan' => $request->rejection_reason
            ]
        );

        return redirect()->back()->with('success', $result['message']);
    }

    public function approveOvertime(OvertimeRequest $overtimeRequest)
    {
        $auth = $this->authorizeApproval($overtimeRequest->employee);
        if (!$auth['allowed']) {
            return redirect()->back()->with('error', $auth['message']);
        }

        $empName = $overtimeRequest->employee?->user?->name ?? 'Karyawan';

        $overtimeRequest->update([
            'status' => 'approved',
            'approved_by' => Auth::id(),
        ]);

        ActivityLog::record(
            'Persetujuan Lembur',
            'APPROVE',
            "Menyetujui pengajuan lembur {$empName} ({$overtimeRequest->duration_hours} Jam)",
            [
                'deskripsi' => "Pengajuan lembur {$empName} ({$overtimeRequest->duration_hours} Jam) pada tanggal {$overtimeRequest->date->format('d/m/Y')} telah disetujui."
            ]
        );

        return redirect()->back()->with('success', 'Pengajuan lembur berhasil disetujui.');
    }

    public function rejectOvertime(Request $request, OvertimeRequest $overtimeRequest)
    {
        $auth = $this->authorizeApproval($overtimeRequest->employee);
        if (!$auth['allowed']) {
            return redirect()->back()->with('error', $auth['message']);
        }

        $request->validate(['rejection_reason' => 'required|string']);
        $empName = $overtimeRequest->employee?->user?->name ?? 'Karyawan';

        $overtimeRequest->update([
            'status' => 'rejected',
            'approved_by' => Auth::id(),
            'rejection_reason' => $request->rejection_reason,
        ]);

        ActivityLog::record(
            'Persetujuan Lembur',
            'REJECT',
            "Menolak pengajuan lembur {$empName}",
            [
                'alasan_penolakan' => $request->rejection_reason
            ]
        );

        return redirect()->back()->with('success', 'Pengajuan lembur ditolak.');
    }

    public function approveCorrection(AttendanceCorrection $correction)
    {
        $auth = $this->authorizeApproval($correction->employee);
        if (!$auth['allowed']) {
            return redirect()->back()->with('error', $auth['message']);
        }

        $empName = $correction->employee?->user?->name ?? 'Karyawan';

        $correction->update([
            'status' => 'approved',
            'approved_by' => Auth::id(),
        ]);

        // Update or create corresponding attendance record
        Attendance::updateOrCreate(
            ['employee_id' => $correction->employee_id, 'date' => $correction->date->format('Y-m-d')],
            [
                'clock_in' => $correction->proposed_clock_in,
                'clock_out' => $correction->proposed_clock_out,
                'status' => 'present',
                'notes' => 'Koreksi Disetujui: ' . $correction->reason,
            ]
        );

        ActivityLog::record(
            'Persetujuan Koreksi',
            'APPROVE',
            "Menyetujui koreksi absensi {$empName} untuk tanggal " . ($correction->date ? $correction->date->format('d/m/Y') : '-'),
            [
                'deskripsi' => "Koreksi presensi {$empName} untuk tanggal " . ($correction->date ? $correction->date->format('d/m/Y') : '-') . " disetujui (Clock In: " . ($correction->proposed_clock_in ?? '-') . ", Clock Out: " . ($correction->proposed_clock_out ?? '-') . ")."
            ]
        );

        return redirect()->back()->with('success', 'Koreksi presensi disetujui dan data kehadiran diperbarui.');
    }

    public function rejectCorrection(Request $request, AttendanceCorrection $correction)
    {
        $auth = $this->authorizeApproval($correction->employee);
        if (!$auth['allowed']) {
            return redirect()->back()->with('error', $auth['message']);
        }

        $request->validate(['rejection_reason' => 'required|string']);
        $empName = $correction->employee?->user?->name ?? 'Karyawan';

        $correction->update([
            'status' => 'rejected',
            'approved_by' => Auth::id(),
            'rejection_reason' => $request->rejection_reason,
        ]);

        ActivityLog::record(
            'Persetujuan Koreksi',
            'REJECT',
            "Menolak koreksi absensi {$empName}",
            [
                'alasan_penolakan' => $request->rejection_reason
            ]
        );

        return redirect()->back()->with('success', 'Koreksi presensi ditolak.');
    }
}
