<?php

namespace Tests\Feature;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RolePermissionBoundaryTest extends TestCase
{
    use RefreshDatabase;

    private User $superAdmin;
    private User $hrManager;
    private User $supervisor;
    private User $employee;

    protected function setUp(): void
    {
        parent::setUp();

        $roleSuper = Role::create(['name' => 'Super Admin']);
        $roleHR = Role::create(['name' => 'HR Manager']);
        $roleSpv = Role::create(['name' => 'Supervisor']);
        $roleEmp = Role::create(['name' => 'Employee']);

        $this->superAdmin = User::factory()->create(['name' => 'Super Admin User']);
        $this->superAdmin->assignRole($roleSuper);

        $this->hrManager = User::factory()->create(['name' => 'HR Manager User']);
        $this->hrManager->assignRole($roleHR);

        $this->supervisor = User::factory()->create(['name' => 'Supervisor User']);
        $this->supervisor->assignRole($roleSpv);

        $this->employee = User::factory()->create(['name' => 'Employee User']);
        $this->employee->assignRole($roleEmp);
    }

    public function test_supervisor_can_access_shared_admin_pages(): void
    {
        // 1. Dashboard
        $response = $this->actingAs($this->supervisor)->get(route('admin.dashboard'));
        $response->assertSuccessful();

        // 2. Attendance Log (Index only)
        $response = $this->actingAs($this->supervisor)->get(route('admin.attendance.index'));
        $response->assertSuccessful();

        // 3. Reports Index
        $response = $this->actingAs($this->supervisor)->get(route('admin.reports.index'));
        $response->assertSuccessful();

        // 4. Employees Index
        $response = $this->actingAs($this->supervisor)->get(route('admin.employees.index'));
        $response->assertSuccessful();

        // 5. Approvals Index
        $response = $this->actingAs($this->supervisor)->get(route('admin.approvals.index'));
        $response->assertSuccessful();
    }

    public function test_supervisor_is_forbidden_from_master_data_and_restricted_actions(): void
    {
        // 1. Cannot access branches CRUD
        $response = $this->actingAs($this->supervisor)->get(route('admin.branches.index'));
        $response->assertForbidden();

        // 2. Cannot access shifts CRUD
        $response = $this->actingAs($this->supervisor)->get(route('admin.shifts.index'));
        $response->assertForbidden();

        // 3. Cannot create employees
        $response = $this->actingAs($this->supervisor)->get(route('admin.employees.create'));
        $response->assertForbidden();

        // 4. Cannot create manual attendance
        $response = $this->actingAs($this->supervisor)->get(route('admin.attendance.create'));
        $response->assertForbidden();

        // 5. Cannot export reports to Excel or PDF
        $response = $this->actingAs($this->supervisor)->get(route('admin.reports.excel'));
        $response->assertForbidden();

        $response = $this->actingAs($this->supervisor)->get(route('admin.reports.pdf'));
        $response->assertForbidden();
    }

    public function test_hr_manager_and_super_admin_can_access_all_master_data(): void
    {
        foreach ([$this->superAdmin, $this->hrManager] as $user) {
            $this->actingAs($user)->get(route('admin.branches.index'))->assertSuccessful();
            $this->actingAs($user)->get(route('admin.shifts.index'))->assertSuccessful();
            $this->actingAs($user)->get(route('admin.employees.create'))->assertSuccessful();
            $this->actingAs($user)->get(route('admin.attendance.create'))->assertSuccessful();
        }
    }

    public function test_regular_employee_cannot_access_any_admin_route(): void
    {
        $response = $this->actingAs($this->employee)->get(route('admin.dashboard'));
        $response->assertForbidden();

        $response = $this->actingAs($this->employee)->get(route('admin.reports.index'));
        $response->assertForbidden();
    }
}
