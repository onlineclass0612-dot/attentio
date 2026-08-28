<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\OvertimeRequest;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OvertimeController extends Controller
{
    public function index()
    {
        $employee = Auth::user()->employee;
        $overtimes = OvertimeRequest::with('approver')
            ->where('employee_id', $employee->id)
            ->orderBy('date', 'desc')
            ->get();

        return view('employee.overtime.index', compact('overtimes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'start_time' => 'required',
            'end_time' => 'required|after:start_time',
            'task_description' => 'required|string|min:5',
        ]);

        $employee = Auth::user()->employee;
        
        $start = Carbon::parse($validated['date'] . ' ' . $validated['start_time']);
        $end = Carbon::parse($validated['date'] . ' ' . $validated['end_time']);
        $durationHours = round($end->diffInMinutes($start) / 60, 2);

        OvertimeRequest::create([
            'employee_id' => $employee->id,
            'date' => $validated['date'],
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
            'duration_hours' => $durationHours,
            'task_description' => $validated['task_description'],
            'status' => 'pending',
        ]);

        return redirect()->route('employee.overtime.index')->with('success', 'Pengajuan lembur berhasil dikirim.');
    }
}
