<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\Branch;
use App\Models\Employee;
use App\Models\Shift;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AttendanceService
{
    public function __construct(
        protected GeolocationService $geoService
    ) {}

    /**
     * Process Clock-In for an employee.
     */
    public function processClockIn(Employee $employee, float $lat, float $lon, ?string $photoData = null, ?string $notes = null): array
    {
        $today = Carbon::today()->format('Y-m-d');
        $now = Carbon::now();

        // 1. Check existing attendance for today
        $attendance = Attendance::where('employee_id', $employee->id)
            ->where('date', $today)
            ->first();

        if ($attendance && $attendance->clock_in) {
            return [
                'success' => false,
                'message' => 'Anda sudah melakukan Clock-In hari ini pada pukul ' . Carbon::parse($attendance->clock_in)->format('H:i') . ' WIB.',
            ];
        }

        // 2. Determine Branch & Geofence validation
        $branch = $employee->branch ?? Branch::where('is_active', true)->first();
        $distance = null;

        if ($branch) {
            $geoCheck = $this->geoService->checkGeofence(
                $lat,
                $lon,
                $branch->latitude,
                $branch->longitude,
                $branch->radius_meters
            );

            $distance = $geoCheck['distance_meters'];

            // If branch enforces geofence strictly
            if (!$geoCheck['is_inside']) {
                return [
                    'success' => false,
                    'message' => $geoCheck['message'],
                    'distance' => $distance,
                ];
            }
        }

        // 3. Determine Shift & Calculate Late Minutes
        $shift = $employee->defaultShift ?? Shift::first();
        $lateMinutes = 0;
        $status = 'present';

        if ($shift) {
            $shiftStartTime = Carbon::parse($today . ' ' . $shift->start_time);
            $graceEndTime = $shiftStartTime->copy()->addMinutes($shift->grace_period_minutes);

            if ($now->greaterThan($graceEndTime)) {
                $status = 'late';
                $lateMinutes = $now->diffInMinutes($shiftStartTime);
            }
        }

        // 4. Save Photo if provided
        $photoPath = $this->saveSelfiePhoto($photoData, 'in', $employee->nik);

        // 5. Create or Update Attendance Record
        $attendance = Attendance::updateOrCreate(
            ['employee_id' => $employee->id, 'date' => $today],
            [
                'branch_id' => $branch?->id,
                'shift_id' => $shift?->id,
                'clock_in' => $now->format('H:i:s'),
                'in_latitude' => $lat,
                'in_longitude' => $lon,
                'in_photo' => $photoPath,
                'in_distance_meters' => $distance,
                'status' => $status,
                'late_minutes' => $lateMinutes,
                'notes' => $notes,
            ]
        );

        return [
            'success' => true,
            'message' => $status === 'late'
                ? "Clock-In berhasil! Anda tercatat terlambat {$lateMinutes} menit."
                : "Clock-In berhasil tepat waktu!",
            'attendance' => $attendance,
        ];
    }

    /**
     * Process Clock-Out for an employee.
     */
    public function processClockOut(Employee $employee, float $lat, float $lon, ?string $photoData = null, ?string $notes = null): array
    {
        $today = Carbon::today()->format('Y-m-d');
        $now = Carbon::now();

        $attendance = Attendance::where('employee_id', $employee->id)
            ->where('date', $today)
            ->first();

        if (!$attendance || !$attendance->clock_in) {
            return [
                'success' => false,
                'message' => 'Anda belum melakukan Clock-In hari ini. Silakan Clock-In terlebih dahulu.',
            ];
        }

        if ($attendance->clock_out) {
            return [
                'success' => false,
                'message' => 'Anda sudah melakukan Clock-Out hari ini pada pukul ' . Carbon::parse($attendance->clock_out)->format('H:i') . ' WIB.',
            ];
        }

        // Branch & Geofence
        $branch = $attendance->branch ?? $employee->branch ?? Branch::first();
        $distance = null;

        if ($branch) {
            $geoCheck = $this->geoService->checkGeofence(
                $lat,
                $lon,
                $branch->latitude,
                $branch->longitude,
                $branch->radius_meters
            );

            $distance = $geoCheck['distance_meters'];

            if (!$geoCheck['is_inside']) {
                return [
                    'success' => false,
                    'message' => $geoCheck['message'],
                    'distance' => $distance,
                ];
            }
        }

        // Calculate early leave & work duration
        $shift = $attendance->shift ?? $employee->defaultShift;
        $earlyLeaveMinutes = 0;
        
        if ($shift) {
            $shiftEndTime = Carbon::parse($today . ' ' . $shift->end_time);
            if ($now->lessThan($shiftEndTime)) {
                $earlyLeaveMinutes = $shiftEndTime->diffInMinutes($now);
            }
        }

        $clockInTime = Carbon::parse($today . ' ' . $attendance->clock_in);
        $workDurationMinutes = $now->diffInMinutes($clockInTime);

        // Save Photo
        $photoPath = $this->saveSelfiePhoto($photoData, 'out', $employee->nik);

        $attendance->update([
            'clock_out' => $now->format('H:i:s'),
            'out_latitude' => $lat,
            'out_longitude' => $lon,
            'out_photo' => $photoPath,
            'out_distance_meters' => $distance,
            'early_leave_minutes' => $earlyLeaveMinutes,
            'work_duration_minutes' => $workDurationMinutes,
            'notes' => $notes ? ($attendance->notes ? $attendance->notes . ' | ' . $notes : $notes) : $attendance->notes,
        ]);

        return [
            'success' => true,
            'message' => $earlyLeaveMinutes > 0
                ? "Clock-Out berhasil tercatat. Pulang lebih awal {$earlyLeaveMinutes} menit."
                : "Clock-Out berhasil! Terima kasih atas dedikasi kerja Anda hari ini.",
            'attendance' => $attendance,
        ];
    }

    /**
     * Save base64 encoded photo to public storage disk.
     */
    protected function saveSelfiePhoto(?string $base64Data, string $type, string $nik): ?string
    {
        if (!$base64Data) {
            return null;
        }

        try {
            if (preg_match('/^data:image\/(\w+);base64,/', $base64Data, $typeMatch)) {
                $base64Data = substr($base64Data, strpos($base64Data, ',') + 1);
                $ext = strtolower($typeMatch[1]);
            } else {
                $ext = 'jpg';
            }

            $decoded = base64_decode($base64Data);
            if (!$decoded) {
                return null;
            }

            $filename = "attendances/{$nik}_{$type}_" . time() . '_' . Str::random(6) . ".{$ext}";
            Storage::disk('public')->put($filename, $decoded);

            return $filename;
        } catch (\Throwable $e) {
            return null;
        }
    }
}
