<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DepartmentController extends Controller
{
    public function index(Request $request)
    {
        $query = Department::withCount(['employees', 'positions']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $departments = $query->orderBy('name')->paginate(10)->withQueryString();

        return view('admin.departments.index', compact('departments'));
    }

    public function create()
    {
        return view('admin.departments.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:50', 'unique:departments,code'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        if (!empty($validated['code'])) {
            $validated['code'] = strtoupper($validated['code']);
        }

        $department = Department::create($validated);

        ActivityLog::record(
            'Master Divisi',
            'CREATE',
            "Menambahkan divisi baru: {$department->name} (" . ($department->code ?? '-') . ")",
            $department->toArray()
        );

        return redirect()->route('admin.departments.index')->with('success', 'Divisi baru berhasil ditambahkan.');
    }

    public function edit(Department $department)
    {
        return view('admin.departments.edit', compact('department'));
    }

    public function update(Request $request, Department $department)
    {
        $oldData = $department->only(['name', 'code', 'description']);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:50', Rule::unique('departments', 'code')->ignore($department->id)],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        if (!empty($validated['code'])) {
            $validated['code'] = strtoupper($validated['code']);
        }

        $department->update($validated);

        ActivityLog::record(
            'Master Divisi',
            'UPDATE',
            "Memperbarui data divisi: {$department->name}",
            ['sebelum' => $oldData, 'sesudah' => $department->only(['name', 'code', 'description'])]
        );

        return redirect()->route('admin.departments.index')->with('success', 'Data divisi berhasil diperbarui.');
    }

    public function destroy(Department $department)
    {
        $employeeCount = $department->employees()->count();
        if ($employeeCount > 0) {
            return back()->with('error', "Divisi '{$department->name}' tidak dapat dihapus karena masih memiliki {$employeeCount} karyawan terhubung.");
        }

        $positionsCount = $department->positions()->count();
        if ($positionsCount > 0) {
            return back()->with('error', "Divisi '{$department->name}' tidak dapat dihapus karena masih memiliki {$positionsCount} jabatan terhubung. Silakan hapus atau pindahkan jabatan terlebih dahulu.");
        }

        $deletedInfo = $department->toArray();
        $deptName = $department->name;

        $department->delete();

        ActivityLog::record(
            'Master Divisi',
            'DELETE',
            "Menghapus divisi: {$deptName}",
            $deletedInfo
        );

        return redirect()->route('admin.departments.index')->with('success', 'Divisi berhasil dihapus.');
    }
}
