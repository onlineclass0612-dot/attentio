<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Department;
use App\Models\Position;
use Illuminate\Http\Request;

class PositionController extends Controller
{
    public function index(Request $request)
    {
        $query = Position::with('department')->withCount('employees');

        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        if ($request->filled('level')) {
            $query->where('level', $request->level);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('name', 'like', "%{$search}%");
        }

        $positions = $query->orderBy('name')->paginate(10)->withQueryString();
        $departments = Department::orderBy('name')->get();
        $levels = ['Director', 'Manager', 'Supervisor', 'Staff', 'Intern'];

        return view('admin.positions.index', compact('positions', 'departments', 'levels'));
    }

    public function create()
    {
        $departments = Department::orderBy('name')->get();
        $levels = ['Director', 'Manager', 'Supervisor', 'Staff', 'Intern'];

        return view('admin.positions.create', compact('departments', 'levels'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'department_id' => ['required', 'exists:departments,id'],
            'name' => ['required', 'string', 'max:255'],
            'level' => ['required', 'string', 'max:50'],
        ]);

        $position = Position::create($validated);
        $deptName = $position->department?->name ?? 'Divisi';

        ActivityLog::record(
            'Master Jabatan',
            'CREATE',
            "Menambahkan jabatan baru: {$position->name} (Level: {$position->level}, Divisi: {$deptName})",
            $position->toArray()
        );

        return redirect()->route('admin.positions.index')->with('success', 'Jabatan baru berhasil ditambahkan.');
    }

    public function edit(Position $position)
    {
        $departments = Department::orderBy('name')->get();
        $levels = ['Director', 'Manager', 'Supervisor', 'Staff', 'Intern'];

        return view('admin.positions.edit', compact('position', 'departments', 'levels'));
    }

    public function update(Request $request, Position $position)
    {
        $oldData = $position->only(['department_id', 'name', 'level']);

        $validated = $request->validate([
            'department_id' => ['required', 'exists:departments,id'],
            'name' => ['required', 'string', 'max:255'],
            'level' => ['required', 'string', 'max:50'],
        ]);

        $position->update($validated);
        $deptName = $position->department?->name ?? 'Divisi';

        ActivityLog::record(
            'Master Jabatan',
            'UPDATE',
            "Memperbarui data jabatan: {$position->name} (Divisi: {$deptName})",
            ['sebelum' => $oldData, 'sesudah' => $position->only(['department_id', 'name', 'level'])]
        );

        return redirect()->route('admin.positions.index')->with('success', 'Data jabatan berhasil diperbarui.');
    }

    public function destroy(Position $position)
    {
        $employeeCount = $position->employees()->count();
        if ($employeeCount > 0) {
            return back()->with('error', "Jabatan '{$position->name}' tidak dapat dihapus karena masih memiliki {$employeeCount} karyawan terhubung.");
        }

        $posInfo = $position->toArray();
        $posName = $position->name;

        $position->delete();

        ActivityLog::record(
            'Master Jabatan',
            'DELETE',
            "Menghapus jabatan: {$posName}",
            $posInfo
        );

        return redirect()->route('admin.positions.index')->with('success', 'Jabatan berhasil dihapus.');
    }
}
