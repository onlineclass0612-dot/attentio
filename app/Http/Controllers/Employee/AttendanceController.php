<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\AttendanceCorrection;
use App\Models\Branch;
use App\Services\AttendanceService;
use App\Services\GeolocationService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AttendanceController extends Controller
{
    public function __construct(
        protected AttendanceService $attendanceService,
        protected GeolocationService $geoService
    ) {}

    /**
     * Show Clock-In / Clock-Out page.
     */
    public function create()
    {
        $employee = Auth::user()->employee;
        if (!$employee) {
            return redirect()->route('dashboard')->with('error', 'Profil karyawan tidak ditemukan.');
        }

        $today = Carbon::today()->format('Y-m-d');
        $attendanceToday = Attendance::where('employee_id', $employee->id)
            ->where('date', $today)
            ->first();

        $branch = $employee->branch ?? Branch::where('is_active', true)->first();
        $shift = $employee->defaultShift;

        return view('employee.attendance.create', compact('employee', 'attendanceToday', 'branch', 'shift'));
    }

    /**
     * Store Clock-In
     */
    public function clockIn(Request $request)
    {
        $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'photo' => 'nullable|string',
            'notes' => 'nullable|string|max:255',
        ]);

        $employee = Auth::user()->employee;
        $result = $this->attendanceService->processClockIn(
            $employee,
            (float) $request->latitude,
            (float) $request->longitude,
            $request->photo,
            $request->notes
        );

        if (!$result['success']) {
            return response()->json($result, 422);
        }

        return response()->json($result, 200);
    }

    /**
     * Store Clock-Out
     */
    public function clockOut(Request $request)
    {
        $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'photo' => 'nullable|string',
            'notes' => 'nullable|string|max:255',
        ]);

        $employee = Auth::user()->employee;
        $result = $this->attendanceService->processClockOut(
            $employee,
            (float) $request->latitude,
            (float) $request->longitude,
            $request->photo,
            $request->notes
        );

        if (!$result['success']) {
            return response()->json($result, 422);
        }

        return response()->json($result, 200);
    }

    /**
     * Attendance History
     */
    public function history(Request $request)
    {
        $employee = Auth::user()->employee;
        $month = $request->input('month', Carbon::now()->format('Y-m'));
        
        $startDate = Carbon::parse($month . '-01')->startOfMonth()->format('Y-m-d');
        $endDate = Carbon::parse($month . '-01')->endOfMonth()->format('Y-m-d');

        $attendances = Attendance::with(['branch', 'shift'])
            ->where('employee_id', $employee->id)
            ->whereBetween('date', [$startDate, $endDate])
            ->orderBy('date', 'desc')
            ->get();

        return view('employee.attendance.history', compact('attendances', 'month'));
    }

    /**
     * Submit attendance correction request.
     */
    public function storeCorrection(Request $request)
    {
        $request->validate([
            'date' => 'required|date|before_or_equal:today',
            'proposed_clock_in' => 'nullable',
            'proposed_clock_out' => 'nullable',
            'reason' => 'required|string|min:5',
        ]);

        $employee = Auth::user()->employee;
        $attendance = Attendance::where('employee_id', $employee->id)
            ->where('date', $request->date)
            ->first();

        AttendanceCorrection::create([
            'employee_id' => $employee->id,
            'attendance_id' => $attendance?->id,
            'date' => $request->date,
            'proposed_clock_in' => $request->proposed_clock_in,
            'proposed_clock_out' => $request->proposed_clock_out,
            'reason' => $request->reason,
            'status' => 'pending',
        ]);

        return redirect()->back()->with('success', 'Pengajuan koreksi presensi berhasil dikirim dan menunggu persetujuan HR.');
    }
}
