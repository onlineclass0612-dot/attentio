<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Department;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\Position;
use App\Models\Shift;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class TieredApprovalAndInactiveEmployeeTest extends TestCase
{
    use RefreshDatabase;

    private User $superAdmin;
    private User $hrManager;
    private User $itSupervisor;
    private User $itStaff;
    private User $inactiveStaff;
    private Department $itDept;
    private Department $hrDept;
    private LeaveType $annualLeave;

    protected function setUp(): void
    {
        parent::setUp();

        $roleSuper = Role::create(['name' => 'Super Admin']);
        $roleHR = Role::create(['name' => 'HR Manager']);
        $roleSpv = Role::create(['name' => 'Supervisor']);
        $roleEmp = Role::create(['name' => 'Employee']);

        $branch = Branch::create([
            'name' => 'HQ Jakarta',
            'code' => 'HQ-01',
            'latitude' => -6.2,
            'longitude' => 106.8,
            'radius_meters' => 100,
            'is_active' => true,
        ]);

        $this->itDept = Department::create(['name' => 'Engineering & IT', 'code' => 'ENG']);
        $this->hrDept = Department::create(['name' => 'Human Resources', 'code' => 'HRD']);

        $posSpv = Position::create(['department_id' => $this->itDept->id, 'name' => 'IT Lead', 'level' => 'Supervisor']);
        $posStaff = Position::create(['department_id' => $this->itDept->id, 'name' => 'Software Engineer', 'level' => 'Staff']);
        $posHR = Position::create(['department_id' => $this->hrDept->id, 'name' => 'HR Manager', 'level' => 'Manager']);

        $shift = Shift::create([
            'name' => 'Regular',
            'start_time' => '08:00:00',
            'end_time' => '17:00:00',
            'is_active' => true,
        ]);

        $this->annualLeave = LeaveType::create(['name' => 'Cuti Tahunan', 'code' => 'ANNUAL', 'default_quota' => 12]);

        // 1. Super Admin
        $this->superAdmin = User::factory()->create(['name' => 'System Admin', 'email' => 'admin@attention.test']);
        $this->superAdmin->assignRole($roleSuper);

        // 2. HR Manager
        $this->hrManager = User::factory()->create(['name' => 'Sarah HR', 'email' => 'hr@attention.test']);
        $this->hrManager->assignRole($roleHR);
        $hrEmp = Employee::create([
            'user_id' => $this->hrManager->id,
            'nik' => 'HR001',
            'department_id' => $this->hrDept->id,
            'position_id' => $posHR->id,
            'branch_id' => $branch->id,
            'default_shift_id' => $shift->id,
            'join_date' => '2021-01-01',
            'employment_status' => 'permanent',
            'is_active' => true,
        ]);

        // 3. IT Supervisor
        $this->itSupervisor = User::factory()->create(['name' => 'Hendra IT Spv', 'email' => 'supervisor.eng@attention.test']);
        $this->itSupervisor->assignRole($roleSpv);
        $spvEmp = Employee::create([
            'user_id' => $this->itSupervisor->id,
            'nik' => 'ENG-SPV-01',
            'department_id' => $this->itDept->id,
            'position_id' => $posSpv->id,
            'branch_id' => $branch->id,
            'default_shift_id' => $shift->id,
            'join_date' => '2022-01-01',
            'employment_status' => 'permanent',
            'is_active' => true,
        ]);

        // 4. IT Staff
        $this->itStaff = User::factory()->create(['name' => 'Budi Staff', 'email' => 'staff.eng@attention.test']);
        $this->itStaff->assignRole($roleEmp);
        $staffEmp = Employee::create([
            'user_id' => $this->itStaff->id,
            'nik' => 'ENG-STF-01',
            'department_id' => $this->itDept->id,
            'position_id' => $posStaff->id,
            'branch_id' => $branch->id,
            'default_shift_id' => $shift->id,
            'join_date' => '2023-01-01',
            'employment_status' => 'permanent',
            'is_active' => true,
        ]);

        // 5. Inactive Staff
        $this->inactiveStaff = User::factory()->create(['name' => 'Dedi Nonaktif', 'email' => 'inactive.staff@attention.test']);
        $this->inactiveStaff->assignRole($roleEmp);
        Employee::create([
            'user_id' => $this->inactiveStaff->id,
            'nik' => 'ENG-NONAKTIF',
            'department_id' => $this->itDept->id,
            'position_id' => $posStaff->id,
            'branch_id' => $branch->id,
            'default_shift_id' => $shift->id,
            'join_date' => '2020-01-01',
            'employment_status' => 'contract',
            'is_active' => false,
        ]);
    }

    public function test_inactive_employee_sees_deactivated_card(): void
    {
        $response = $this->actingAs($this->inactiveStaff)->get(route('employee.dashboard'));
        $response->assertStatus(403);
        $response->assertSee('Akses Portal Dinonaktifkan');
        $response->assertSee('Status Kepegawaian Non-Aktif');
        $response->assertSee('Dedi Nonaktif');
        $response->assertSee('Keluar dari Akun (Logout)');
    }

    public function test_inactive_employee_cannot_access_leave_routes(): void
    {
        $response = $this->actingAs($this->inactiveStaff)->get(route('employee.leave.index'));
        $response->assertStatus(403);
        $response->assertSee('Akses Portal Dinonaktifkan');
    }

    public function test_staff_leave_can_be_approved_by_supervisor_hr_and_superadmin(): void
    {
        $staffEmp = $this->itStaff->employee;
        $leave = LeaveRequest::create([
            'employee_id' => $staffEmp->id,
            'leave_type_id' => $this->annualLeave->id,
            'start_date' => '2026-09-01',
            'end_date' => '2026-09-02',
            'total_days' => 2,
            'reason' => 'Liburan keluarga',
            'status' => 'pending',
        ]);

        // IT Supervisor sees it
        $spvResponse = $this->actingAs($this->itSupervisor)->get(route('admin.approvals.index'));
        $spvResponse->assertSuccessful();
        $spvResponse->assertSee('Budi Staff');

        // HR Manager sees it
        $hrResponse = $this->actingAs($this->hrManager)->get(route('admin.approvals.index'));
        $hrResponse->assertSuccessful();
        $hrResponse->assertSee('Budi Staff');

        // IT Supervisor approves it
        $approveResponse = $this->actingAs($this->itSupervisor)->post(route('admin.approvals.leave.approve', $leave));
        $approveResponse->assertSessionHas('success');
        $this->assertEquals('approved', $leave->fresh()->status);
    }

    public function test_supervisor_cannot_approve_own_leave_and_it_escalates_to_hr_and_superadmin(): void
    {
        $spvEmp = $this->itSupervisor->employee;
        $leave = LeaveRequest::create([
            'employee_id' => $spvEmp->id,
            'leave_type_id' => $this->annualLeave->id,
            'start_date' => '2026-09-10',
            'end_date' => '2026-09-11',
            'total_days' => 2,
            'reason' => 'Urusan pribadi supervisor',
            'status' => 'pending',
        ]);

        // 1. Supervisor's own approval list does NOT show their own request
        $spvResponse = $this->actingAs($this->itSupervisor)->get(route('admin.approvals.index'));
        $spvResponse->assertSuccessful();
        $spvResponse->assertDontSee('Urusan pribadi supervisor');

        // 2. Supervisor attempting direct approve on own request is blocked
        $blockedResponse = $this->actingAs($this->itSupervisor)->post(route('admin.approvals.leave.approve', $leave));
        $blockedResponse->assertSessionHas('error');
        $this->assertEquals('pending', $leave->fresh()->status);

        // 3. HR Manager sees supervisor's request and can approve it
        $hrResponse = $this->actingAs($this->hrManager)->get(route('admin.approvals.index'));
        $hrResponse->assertSuccessful();
        $hrResponse->assertSee('Hendra IT Spv');

        $hrApproveResponse = $this->actingAs($this->hrManager)->post(route('admin.approvals.leave.approve', $leave));
        $hrApproveResponse->assertSessionHas('success');
        $this->assertEquals('approved', $leave->fresh()->status);
    }

    public function test_hr_manager_leave_escalates_to_super_admin_only(): void
    {
        $hrEmp = $this->hrManager->employee;
        $leave = LeaveRequest::create([
            'employee_id' => $hrEmp->id,
            'leave_type_id' => $this->annualLeave->id,
            'start_date' => '2026-09-15',
            'end_date' => '2026-09-16',
            'total_days' => 2,
            'reason' => 'Cuti HR Manager',
            'status' => 'pending',
        ]);

        // 1. HR Manager cannot approve own leave
        $hrResponse = $this->actingAs($this->hrManager)->get(route('admin.approvals.index'));
        $hrResponse->assertSuccessful();
        $hrResponse->assertDontSee('Cuti HR Manager');

        $blockedHR = $this->actingAs($this->hrManager)->post(route('admin.approvals.leave.approve', $leave));
        $blockedHR->assertSessionHas('error');
        $this->assertEquals('pending', $leave->fresh()->status);

        // 2. IT Supervisor cannot approve HR Manager leave
        $blockedSpv = $this->actingAs($this->itSupervisor)->post(route('admin.approvals.leave.approve', $leave));
        $blockedSpv->assertSessionHas('error');

        // 3. Super Admin sees and approves HR Manager's leave
        $adminResponse = $this->actingAs($this->superAdmin)->get(route('admin.approvals.index'));
        $adminResponse->assertSuccessful();
        $adminResponse->assertSee('Sarah HR');

        $adminApprove = $this->actingAs($this->superAdmin)->post(route('admin.approvals.leave.approve', $leave));
        $adminApprove->assertSessionHas('success');
        $this->assertEquals('approved', $leave->fresh()->status);
    }
}
