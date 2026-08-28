<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Department;
use App\Models\Employee;
use App\Models\LeaveBalance;
use App\Models\LeaveType;
use App\Models\Position;
use App\Models\Shift;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;

class EmployeeController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $isSupervisorOnly = $user->hasRole('Supervisor') && !$user->hasAnyRole(['Super Admin', 'HR Manager']);
        $spvDepartmentId = $isSupervisorOnly ? $user->employee?->department_id : null;

        $query = Employee::with(['user', 'department', 'position', 'branch', 'defaultShift']);

        if ($spvDepartmentId) {
            $query->where('department_id', $spvDepartmentId);
        } elseif ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nik', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($uq) use ($search) {
                      $uq->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        $employees = $query->latest()->paginate(15);
        $departments = $spvDepartmentId ? Department::where('id', $spvDepartmentId)->get() : Department::all();
        $branches = Branch::all();
        $supervisorDepartment = $spvDepartmentId ? $user->employee?->department : null;

        return view('admin.employees.index', compact('employees', 'departments', 'branches', 'supervisorDepartment'));
    }

    public function create()
    {
        $departments = Department::all();
        $positions = Position::all();
        $branches = Branch::all();
        $shifts = Shift::all();
        $roles = Role::all();

        return view('admin.employees.create', compact('departments', 'positions', 'branches', 'shifts', 'roles'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'nik' => 'required|string|unique:employees,nik',
            'role' => 'required|exists:roles,name',
            'department_id' => 'required|exists:departments,id',
            'position_id' => 'required|exists:positions,id',
            'branch_id' => 'required|exists:branches,id',
            'default_shift_id' => 'required|exists:shifts,id',
            'phone' => 'nullable|string|max:20',
            'gender' => 'required|in:male,female',
            'employment_status' => 'required|in:permanent,contract,probation,intern',
            'join_date' => 'required|date',
            'avatar' => 'nullable|image|max:2048',
        ]);

        DB::transaction(function () use ($request) {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);

            $user->assignRole($request->role);

            $avatarPath = null;
            if ($request->hasFile('avatar')) {
                $avatarPath = $request->file('avatar')->store('avatars', 'public');
            }

            $employee = Employee::create([
                'user_id' => $user->id,
                'nik' => $request->nik,
                'department_id' => $request->department_id,
                'position_id' => $request->position_id,
                'branch_id' => $request->branch_id,
                'default_shift_id' => $request->default_shift_id,
                'phone' => $request->phone,
                'gender' => $request->gender,
                'join_date' => $request->join_date,
                'employment_status' => $request->employment_status,
                'avatar' => $avatarPath,
                'is_active' => true,
            ]);

            // Initialize leave balances for current year
            $leaveTypes = LeaveType::where('is_active', true)->get();
            $year = (int) date('Y');
            foreach ($leaveTypes as $lt) {
                LeaveBalance::create([
                    'employee_id' => $employee->id,
                    'leave_type_id' => $lt->id,
                    'year' => $year,
                    'quota' => $lt->default_quota,
                    'used' => 0,
                    'remaining' => $lt->default_quota,
                ]);
            }

            $deptName = Department::find($request->department_id)?->name ?? '-';
            $posName = Position::find($request->position_id)?->name ?? '-';
            $branchName = Branch::find($request->branch_id)?->name ?? '-';
            $shiftName = Shift::find($request->default_shift_id)?->name ?? '-';

            \App\Models\ActivityLog::record(
                'Data Karyawan',
                'CREATE',
                "Menambahkan karyawan baru: {$user->name} (NIK: {$employee->nik}, Divisi: {$deptName}, Jabatan: {$posName})",
                [
                    'nik' => $employee->nik,
                    'nama_lengkap' => $user->name,
                    'email' => $user->email,
                    'peran_akun' => $request->role,
                    'divisi' => $deptName,
                    'jabatan' => $posName,
                    'kantor_cabang' => $branchName,
                    'shift_kerja' => $shiftName,
                    'status_kepegawaian' => ucfirst($employee->employment_status),
                    'status_aktif' => 'Aktif',
                ]
            );
        });

        return redirect()->route('admin.employees.index')->with('success', 'Data karyawan baru berhasil ditambahkan.');
    }

    public function edit(Employee $employee)
    {
        $employee->load('user');
        $departments = Department::all();
        $positions = Position::all();
        $branches = Branch::all();
        $shifts = Shift::all();
        $roles = Role::all();

        return view('admin.employees.edit', compact('employee', 'departments', 'positions', 'branches', 'shifts', 'roles'));
    }

    public function update(Request $request, Employee $employee)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $employee->user_id,
            'password' => 'nullable|min:6',
            'nik' => 'required|string|unique:employees,nik,' . $employee->id,
            'role' => 'required|exists:roles,name',
            'department_id' => 'required|exists:departments,id',
            'position_id' => 'required|exists:positions,id',
            'branch_id' => 'required|exists:branches,id',
            'default_shift_id' => 'required|exists:shifts,id',
            'phone' => 'nullable|string|max:20',
            'gender' => 'required|in:male,female',
            'employment_status' => 'required|in:permanent,contract,probation,intern',
            'join_date' => 'required|date',
            'avatar' => 'nullable|image|max:2048',
            'is_active' => 'required|boolean',
        ]);

        DB::transaction(function () use ($request, $employee) {
            $employee->load(['user.roles', 'department', 'position', 'branch', 'defaultShift']);
            $user = $employee->user;

            $statusLabels = [
                'permanent' => 'Pegawai Tetap (Permanent)',
                'contract' => 'Pegawai Kontrak (Contract)',
                'probation' => 'Masa Percobaan (Probation)',
                'intern' => 'Magang (Intern)',
            ];

            // 1. Capture complete state BEFORE update
            $oldRole = $user->roles->first()?->name ?? 'Staff';
            $beforeData = [
                'nama_lengkap' => $user->name,
                'email' => $user->email,
                'peran_akun' => $oldRole,
                'nik' => $employee->nik,
                'divisi' => $employee->department?->name ?? '-',
                'jabatan' => $employee->position?->name ?? '-',
                'kantor_cabang' => $employee->branch?->name ?? '-',
                'shift_kerja' => $employee->defaultShift?->name ?? '-',
                'status_kepegawaian' => $statusLabels[$employee->employment_status] ?? ucfirst($employee->employment_status ?? '-'),
                'status_aktif' => $employee->is_active ? 'Aktif' : 'Non-Aktif',
                'nomor_telepon' => $employee->phone ?? '-',
                'jenis_kelamin' => $employee->gender === 'male' ? 'Laki-laki' : 'Perempuan',
                'tanggal_bergabung' => $employee->join_date ? $employee->join_date->format('d/m/Y') : '-',
            ];

            // 2. Perform Database updates
            $userData = [
                'name' => $request->name,
                'email' => $request->email,
            ];
            if ($request->filled('password')) {
                $userData['password'] = Hash::make($request->password);
            }
            $user->update($userData);
            $user->syncRoles([$request->role]);

            $empData = [
                'nik' => $request->nik,
                'department_id' => $request->department_id,
                'position_id' => $request->position_id,
                'branch_id' => $request->branch_id,
                'default_shift_id' => $request->default_shift_id,
                'phone' => $request->phone,
                'gender' => $request->gender,
                'join_date' => $request->join_date,
                'employment_status' => $request->employment_status,
                'is_active' => $request->boolean('is_active'),
            ];

            if ($request->hasFile('avatar')) {
                if ($employee->avatar) {
                    Storage::disk('public')->delete($employee->avatar);
                }
                $empData['avatar'] = $request->file('avatar')->store('avatars', 'public');
            }

            $employee->update($empData);

            // 3. Capture state AFTER update
            $newDept = Department::find($request->department_id)?->name ?? '-';
            $newPos = Position::find($request->position_id)?->name ?? '-';
            $newBranch = Branch::find($request->branch_id)?->name ?? '-';
            $newShift = Shift::find($request->default_shift_id)?->name ?? '-';
            $newJoinDate = \Carbon\Carbon::parse($request->join_date)->format('d/m/Y');

            $afterData = [
                'nama_lengkap' => $request->name,
                'email' => $request->email,
                'peran_akun' => $request->role,
                'nik' => $request->nik,
                'divisi' => $newDept,
                'jabatan' => $newPos,
                'kantor_cabang' => $newBranch,
                'shift_kerja' => $newShift,
                'status_kepegawaian' => $statusLabels[$request->employment_status] ?? ucfirst($request->employment_status),
                'status_aktif' => $request->boolean('is_active') ? 'Aktif' : 'Non-Aktif',
                'nomor_telepon' => $request->phone ?? '-',
                'jenis_kelamin' => $request->gender === 'male' ? 'Laki-laki' : 'Perempuan',
                'tanggal_bergabung' => $newJoinDate,
            ];

            // 4. Calculate detailed diff summary
            $changedLabels = [];
            foreach ($afterData as $k => $newVal) {
                $oldVal = $beforeData[$k] ?? null;
                if ($oldVal !== $newVal) {
                    $label = ucwords(str_replace('_', ' ', $k));
                    $changedLabels[] = "{$label}: '{$oldVal}' → '{$newVal}'";
                }
            }

            $changesText = count($changedLabels) > 0
                ? " [Perubahan: " . implode(', ', array_slice($changedLabels, 0, 3)) . (count($changedLabels) > 3 ? ', dsb.' : '') . "]"
                : " (Tidak ada nilai yang diubah)";

            \App\Models\ActivityLog::record(
                'Data Karyawan',
                'UPDATE',
                "Memperbarui data profil karyawan: {$user->name} (NIK: {$request->nik}){$changesText}",
                [
                    'sebelum' => $beforeData,
                    'sesudah' => $afterData,
                    'perubahan' => $changedLabels,
                ]
            );
        });

        return redirect()->route('admin.employees.index')->with('success', 'Data karyawan berhasil diperbarui.');
    }

    public function destroy(Employee $employee)
    {
        $user = $employee->user;
        $name = $user?->name ?? 'Karyawan';
        $email = $user?->email ?? '-';
        $nik = $employee->nik;
        $deptName = $employee->department?->name ?? '-';
        $posName = $employee->position?->name ?? '-';

        $employee->delete();
        if ($user) {
            $user->delete();
        }

        \App\Models\ActivityLog::record(
            'Data Karyawan',
            'DELETE',
            "Menghapus akun dan profil karyawan: {$name} (NIK: {$nik}, Divisi: {$deptName})",
            [
                'nik' => $nik,
                'nama_lengkap' => $name,
                'email' => $email,
                'divisi' => $deptName,
                'jabatan' => $posName,
            ]
        );

        return redirect()->route('admin.employees.index')->with('success', 'Data karyawan berhasil dihapus.');
    }
}
