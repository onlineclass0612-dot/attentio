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

            \App\Models\ActivityLog::record(
                'Data Karyawan',
                'CREATE',
                "Menambahkan karyawan baru: {$user->name} (NIK: {$employee->nik}, Role: {$request->role})",
                ['nik' => $employee->nik, 'name' => $user->name, 'email' => $user->email]
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
            $user = $employee->user;
            $oldName = $user->name;
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

            \App\Models\ActivityLog::record(
                'Data Karyawan',
                'UPDATE',
                "Memperbarui profil data karyawan: {$user->name} (NIK: {$employee->nik})",
                ['nik' => $employee->nik, 'name' => $user->name, 'status' => $empData['employment_status']]
            );
        });

        return redirect()->route('admin.employees.index')->with('success', 'Data karyawan berhasil diperbarui.');
    }

    public function destroy(Employee $employee)
    {
        $user = $employee->user;
        $name = $user?->name ?? 'Karyawan';
        $nik = $employee->nik;

        $employee->delete();
        if ($user) {
            $user->delete();
        }

        \App\Models\ActivityLog::record(
            'Data Karyawan',
            'DELETE',
            "Menghapus akun dan profil karyawan: {$name} (NIK: {$nik})",
            ['nik' => $nik, 'name' => $name]
        );

        return redirect()->route('admin.employees.index')->with('success', 'Data karyawan berhasil dihapus.');
    }
}
