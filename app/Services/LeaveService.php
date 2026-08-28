<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\LeaveBalance;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class LeaveService
{
    /**
     * Submit a leave or permission request.
     */
    public function submitRequest(Employee $employee, array $data, ?UploadedFile $attachment = null): array
    {
        $leaveType = LeaveType::findOrFail($data['leave_type_id']);
        $startDate = Carbon::parse($data['start_date']);
        $endDate = Carbon::parse($data['end_date']);

        if ($endDate->lessThan($startDate)) {
            return ['success' => false, 'message' => 'Tanggal akhir tidak boleh lebih awal dari tanggal mulai.'];
        }

        // Calculate total business days (excluding weekends)
        $period = CarbonPeriod::create($startDate, $endDate);
        $totalDays = 0;
        foreach ($period as $date) {
            if (!$date->isWeekend()) {
                $totalDays++;
            }
        }

        if ($totalDays <= 0) {
            return ['success' => false, 'message' => 'Jumlah hari cuti/izin tidak valid (jatuh pada akhir pekan).'];
        }

        // Check quota if leave type has quotas
        $year = (int) $startDate->format('Y');
        $balance = LeaveBalance::where('employee_id', $employee->id)
            ->where('leave_type_id', $leaveType->id)
            ->where('year', $year)
            ->first();

        if ($balance && $balance->remaining < $totalDays) {
            return [
                'success' => false,
                'message' => "Sisa kuota {$leaveType->name} Anda tidak mencukupi. Sisa: {$balance->remaining} hari, diajukan: {$totalDays} hari.",
            ];
        }

        // Handle attachment upload
        $attachmentPath = null;
        if ($attachment) {
            $attachmentPath = $attachment->store('leaves', 'public');
        } elseif ($leaveType->requires_attachment) {
            return [
                'success' => false,
                'message' => "Tipe pengajuan {$leaveType->name} mewajibkan melampirkan berkas/surat pendukung.",
            ];
        }

        $leaveRequest = LeaveRequest::create([
            'employee_id' => $employee->id,
            'leave_type_id' => $leaveType->id,
            'start_date' => $startDate->format('Y-m-d'),
            'end_date' => $endDate->format('Y-m-d'),
            'total_days' => $totalDays,
            'reason' => $data['reason'],
            'attachment' => $attachmentPath,
            'status' => 'pending',
        ]);

        return [
            'success' => true,
            'message' => 'Pengajuan berhasil dikirim dan menunggu persetujuan atasan/HR.',
            'request' => $leaveRequest,
        ];
    }

    /**
     * Approve leave request and update balances & attendance records.
     */
    public function approveRequest(LeaveRequest $request, User $approver): array
    {
        if ($request->status === 'approved') {
            return ['success' => false, 'message' => 'Pengajuan sudah pernah disetujui sebelumnya.'];
        }

        DB::transaction(function () use ($request, $approver) {
            $request->update([
                'status' => 'approved',
                'approved_by' => $approver->id,
            ]);

            // Deduct balance
            $year = (int) Carbon::parse($request->start_date)->format('Y');
            $balance = LeaveBalance::where('employee_id', $request->employee_id)
                ->where('leave_type_id', $request->leave_type_id)
                ->where('year', $year)
                ->first();

            if ($balance) {
                $balance->increment('used', $request->total_days);
                $balance->decrement('remaining', $request->total_days);
            }

            // Generate attendance records for those dates
            $period = CarbonPeriod::create($request->start_date, $request->end_date);
            $status = $request->leaveType->code === 'SICK' ? 'sick' : 'leave';

            foreach ($period as $dt) {
                if (!$dt->isWeekend()) {
                    Attendance::updateOrCreate(
                        ['employee_id' => $request->employee_id, 'date' => $dt->format('Y-m-d')],
                        [
                            'status' => $status,
                            'notes' => "{$request->leaveType->name}: {$request->reason}",
                        ]
                    );
                }
            }
        });

        return ['success' => true, 'message' => 'Pengajuan cuti/izin berhasil disetujui.'];
    }

    /**
     * Reject leave request.
     */
    public function rejectRequest(LeaveRequest $request, User $approver, string $rejectionReason): array
    {
        $request->update([
            'status' => 'rejected',
            'approved_by' => $approver->id,
            'rejection_reason' => $rejectionReason,
        ]);

        return ['success' => true, 'message' => 'Pengajuan cuti/izin berhasil ditolak.'];
    }
}
