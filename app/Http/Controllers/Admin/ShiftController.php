<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Shift;
use Illuminate\Http\Request;

class ShiftController extends Controller
{
    public function index()
    {
        $shifts = Shift::withCount('attendances')->get();
        return view('admin.shifts.index', compact('shifts'));
    }

    public function create()
    {
        return view('admin.shifts.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'start_time' => 'required',
            'end_time' => 'required',
            'grace_period_minutes' => 'required|integer|min:0|max:120',
            'is_overnight' => 'required|boolean',
            'is_active' => 'required|boolean',
        ]);

        $shift = Shift::create($validated);

        ActivityLog::record(
            'Master Shift',
            'CREATE',
            "Menambahkan master shift baru: {$shift->name} ({$shift->start_time} - {$shift->end_time})",
            $shift->toArray()
        );

        return redirect()->route('admin.shifts.index')->with('success', 'Master shift berhasil ditambahkan.');
    }

    public function edit(Shift $shift)
    {
        return view('admin.shifts.edit', compact('shift'));
    }

    public function update(Request $request, Shift $shift)
    {
        $oldData = $shift->only(['name', 'start_time', 'end_time', 'grace_period_minutes', 'is_overnight', 'is_active']);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'start_time' => 'required',
            'end_time' => 'required',
            'grace_period_minutes' => 'required|integer|min:0|max:120',
            'is_overnight' => 'required|boolean',
            'is_active' => 'required|boolean',
        ]);

        $shift->update($validated);

        ActivityLog::record(
            'Master Shift',
            'UPDATE',
            "Memperbarui master shift: {$shift->name}",
            ['sebelum' => $oldData, 'sesudah' => $shift->only(['name', 'start_time', 'end_time', 'grace_period_minutes', 'is_overnight', 'is_active'])]
        );

        return redirect()->route('admin.shifts.index')->with('success', 'Master shift berhasil diperbarui.');
    }

    public function destroy(Shift $shift)
    {
        $shiftInfo = $shift->toArray();
        $shiftName = $shift->name;

        $shift->delete();

        ActivityLog::record(
            'Master Shift',
            'DELETE',
            "Menghapus master shift: {$shiftName}",
            $shiftInfo
        );

        return redirect()->route('admin.shifts.index')->with('success', 'Master shift berhasil dihapus.');
    }
}
