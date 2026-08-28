<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\AttendanceCorrection;
use App\Models\Branch;
use App\Models\Department;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\OvertimeRequest;
use App\Models\Position;
use App\Models\Shift;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SupervisorDepartmentScopingTest extends TestCase
{
    use RefreshDatabase;

    private User $spvEng;
    private User $spvFin;
    private User $admin;
    private Department $deptEng;
    private Department $deptFin;
    private Employee $empEng;
    private Employee $empFin;
    private LeaveType $leaveType;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware([
            \Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class,
            \Illuminate\Foundation\Http\Middleware\PreventRequestForgery::class,
        ]);

        $superAdminRole = Role::create(['name' => 'Super Admin']);
        $spvRole = Role::create(['name' => 'Supervisor']);
        $empRole = Role::create(['name' => 'Employee']);

        $branch = Branch::create([
            'code' => 'HQ-01',
            'name' => 'HQ Jakarta',
            'latitude' => -6.2,
            'longitude' => 106.8,
            'radius_meters' => 100,
            'is_active' => true,
        ]);

        $shift = Shift::create(['name' => 'General', 'start_time' => '08:00:00', 'end_time' => '17:00:00', 'is_active' => true]);
        $this->leaveType = LeaveType::create(['code' => 'ANNUAL', 'name' => 'Cuti Tahunan', 'default_quota' => 12, 'is_active' => true]);

        // Departments
        $this->deptEng = Department::create(['name' => 'Engineering', 'code' => 'ENG']);
        $this->deptFin = Department::create(['name' => 'Finance', 'code' => 'FIN']);

        $posEngLead = Position::create(['department_id' => $this->deptEng->id, 'name' => 'Engineering Lead', 'level' => 'Supervisor']);
        $posEngStaff = Position::create(['department_id' => $this->deptEng->id, 'name' => 'Backend Dev', 'level' => 'Staff']);

        $posFinLead = Position::create(['department_id' => $this->deptFin->id, 'name' => 'Finance Lead', 'level' => 'Supervisor']);
        $posFinStaff = Position::create(['department_id' => $this->deptFin->id, 'name' => 'Finance Staff', 'level' => 'Staff']);

        // 1. Super Admin
        $this->admin = User::factory()->create(['name' => 'System Admin']);
        $this->admin->assignRole($superAdminRole);

        // 2. Supervisor Engineering
        $this->spvEng = User::factory()->create(['name' => 'Spv Engineering']);
        $this->spvEng->assignRole($spvRole);
        Employee::create([
            'user_id' => $this->spvEng->id,
            'department_id' => $this->deptEng->id,
            'position_id' => $posEngLead->id,
            'branch_id' => $branch->id,
            'default_shift_id' => $shift->id,
            'nik' => 'SPV-ENG',
            'join_date' => '2024-01-01',
            'employment_status' => 'permanent',
            'is_active' => true,
        ]);

        // 3. Supervisor Finance
        $this->spvFin = User::factory()->create(['name' => 'Spv Finance']);
        $this->spvFin->assignRole($spvRole);
        Employee::create([
            'user_id' => $this->spvFin->id,
            'department_id' => $this->deptFin->id,
            'position_id' => $posFinLead->id,
            'branch_id' => $branch->id,
            'default_shift_id' => $shift->id,
            'nik' => 'SPV-FIN',
            'join_date' => '2024-01-01',
            'employment_status' => 'permanent',
            'is_active' => true,
        ]);

        // 4. Employee Engineering
        $userEmpEng = User::factory()->create(['name' => 'Budi Engineering']);
        $userEmpEng->assignRole($empRole);
        $this->empEng = Employee::create([
            'user_id' => $userEmpEng->id,
            'department_id' => $this->deptEng->id,
            'position_id' => $posEngStaff->id,
            'branch_id' => $branch->id,
            'default_shift_id' => $shift->id,
            'nik' => 'EMP-ENG',
            'join_date' => '2024-01-01',
            'employment_status' => 'permanent',
            'is_active' => true,
        ]);

        // 5. Employee Finance
        $userEmpFin = User::factory()->create(['name' => 'Andi Finance']);
        $userEmpFin->assignRole($empRole);
        $this->empFin = Employee::create([
            'user_id' => $userEmpFin->id,
            'department_id' => $this->deptFin->id,
            'position_id' => $posFinStaff->id,
            'branch_id' => $branch->id,
            'default_shift_id' => $shift->id,
            'nik' => 'EMP-FIN',
            'join_date' => '2024-01-01',
            'employment_status' => 'permanent',
            'is_active' => true,
        ]);
    }

    public function test_supervisor_only_sees_their_department_employees(): void
    {
        $response = $this->actingAs($this->spvEng)->get(route('admin.employees.index'));
        $response->assertSuccessful();
        $response->assertSee('Budi Engineering');
        $response->assertDontSee('Andi Finance');
    }

    public function test_supervisor_only_sees_their_department_approvals(): void
    {
        $leaveEng = LeaveRequest::create([
            'employee_id' => $this->empEng->id,
            'leave_type_id' => $this->leaveType->id,
            'start_date' => Carbon::now()->addDays(2)->format('Y-m-d'),
            'end_date' => Carbon::now()->addDays(3)->format('Y-m-d'),
            'total_days' => 2,
            'reason' => 'Keperluan Dev',
            'status' => 'pending',
        ]);

        $leaveFin = LeaveRequest::create([
            'employee_id' => $this->empFin->id,
            'leave_type_id' => $this->leaveType->id,
            'start_date' => Carbon::now()->addDays(2)->format('Y-m-d'),
            'end_date' => Carbon::now()->addDays(3)->format('Y-m-d'),
            'total_days' => 2,
            'reason' => 'Keperluan Audit Pajak',
            'status' => 'pending',
        ]);

        // Spv Engineering visits approvals page
        $response = $this->actingAs($this->spvEng)->get(route('admin.approvals.index'));
        $response->assertSuccessful();
        $response->assertSee('Budi Engineering');
        $response->assertSee('Keperluan Dev');
        $response->assertDontSee('Andi Finance');
        $response->assertDontSee('Keperluan Audit Pajak');
    }

    public function test_supervisor_cannot_approve_leave_of_different_department(): void
    {
        $leaveFin = LeaveRequest::create([
            'employee_id' => $this->empFin->id,
            'leave_type_id' => $this->leaveType->id,
            'start_date' => Carbon::now()->addDays(2)->format('Y-m-d'),
            'end_date' => Carbon::now()->addDays(3)->format('Y-m-d'),
            'total_days' => 2,
            'reason' => 'Keperluan Audit Pajak',
            'status' => 'pending',
        ]);

        // Spv Engineering attempts to approve Spv Finance's employee
        $response = $this->actingAs($this->spvEng)->post(route('admin.approvals.leave.approve', $leaveFin->id));
        $response->assertRedirect();
        $response->assertSessionHas('error');

        $this->assertEquals('pending', $leaveFin->fresh()->status);
    }

    public function test_admin_sees_all_departments_without_restriction(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.employees.index'));
        $response->assertSuccessful();
        $response->assertSee('Budi Engineering');
        $response->assertSee('Andi Finance');
    }
}
