<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Branch;
use Illuminate\Http\Request;

class BranchController extends Controller
{
    public function index()
    {
        $branches = Branch::withCount('employees')->get();
        return view('admin.branches.index', compact('branches'));
    }

    public function create()
    {
        return view('admin.branches.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|unique:branches,code',
            'address' => 'nullable|string',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'radius_meters' => 'required|integer|min:10|max:5000',
            'is_active' => 'required|boolean',
        ]);

        $branch = Branch::create($validated);

        ActivityLog::record(
            'Master Kantor',
            'CREATE',
            "Menambahkan kantor cabang baru: {$branch->name} ({$branch->code}) - Radius: {$branch->radius_meters}m",
            $branch->toArray()
        );

        return redirect()->route('admin.branches.index')->with('success', 'Lokasi kantor baru berhasil ditambahkan.');
    }

    public function edit(Branch $branch)
    {
        return view('admin.branches.edit', compact('branch'));
    }

    public function update(Request $request, Branch $branch)
    {
        $oldData = $branch->only(['name', 'code', 'address', 'latitude', 'longitude', 'radius_meters', 'is_active']);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|unique:branches,code,' . $branch->id,
            'address' => 'nullable|string',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'radius_meters' => 'required|integer|min:10|max:5000',
            'is_active' => 'required|boolean',
        ]);

        $branch->update($validated);

        ActivityLog::record(
            'Master Kantor',
            'UPDATE',
            "Memperbarui data kantor cabang: {$branch->name} (Geofence Radius: {$branch->radius_meters}m)",
            ['sebelum' => $oldData, 'sesudah' => $branch->only(['name', 'code', 'address', 'latitude', 'longitude', 'radius_meters', 'is_active'])]
        );

        return redirect()->route('admin.branches.index')->with('success', 'Lokasi kantor berhasil diperbarui.');
    }

    public function destroy(Branch $branch)
    {
        if ($branch->employees()->count() > 0) {
            return redirect()->back()->with('error', 'Kantor ini tidak dapat dihapus karena masih memiliki karyawan aktif.');
        }

        $branchInfo = $branch->toArray();
        $branchName = $branch->name;

        $branch->delete();

        ActivityLog::record(
            'Master Kantor',
            'DELETE',
            "Menghapus lokasi kantor: {$branchName}",
            $branchInfo
        );

        return redirect()->route('admin.branches.index')->with('success', 'Lokasi kantor berhasil dihapus.');
    }
}
