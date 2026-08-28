<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Employee;
use App\Models\Position;
use App\Models\Shift;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class DepartmentManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $hrManager;
    private User $supervisor;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware([
            \Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class,
            \Illuminate\Foundation\Http\Middleware\PreventRequestForgery::class,
        ]);

        $superAdminRole = Role::create(['name' => 'Super Admin']);
        $hrRole = Role::create(['name' => 'HR Manager']);
        $spvRole = Role::create(['name' => 'Supervisor']);

        $this->admin = User::factory()->create();
        $this->admin->assignRole($superAdminRole);

        $this->hrManager = User::factory()->create();
        $this->hrManager->assignRole($hrRole);

        $this->supervisor = User::factory()->create();
        $this->supervisor->assignRole($spvRole);
    }

    public function test_admin_can_view_departments_index(): void
    {
        $dept = Department::create(['name' => 'Engineering', 'code' => 'ENG']);

        $response = $this->actingAs($this->admin)->get(route('admin.departments.index'));
        $response->assertSuccessful();
        $response->assertSee('Master Divisi / Departemen');
        $response->assertSee('ENG');
    }

    public function test_hr_manager_can_create_new_department(): void
    {
        $payload = [
            'name' => 'Marketing & Sales',
            'code' => 'mkt',
            'description' => 'Divisi pemasaran dan penjualan produk perusahaan',
        ];

        $response = $this->actingAs($this->hrManager)->post(route('admin.departments.store'), $payload);
        $response->assertRedirect(route('admin.departments.index'));

        $this->assertDatabaseHas('departments', [
            'name' => 'Marketing & Sales',
            'code' => 'MKT', // converted to uppercase
        ]);
    }

    public function test_admin_can_update_department(): void
    {
        $dept = Department::create(['name' => 'Finance Dept', 'code' => 'FIN']);

        $payload = [
            'name' => 'Finance & Accounting',
            'code' => 'FA',
            'description' => 'Divisi keuangan perusahaan',
        ];

        $response = $this->actingAs($this->admin)->put(route('admin.departments.update', $dept->id), $payload);
        $response->assertRedirect(route('admin.departments.index'));

        $this->assertDatabaseHas('departments', [
            'id' => $dept->id,
            'name' => 'Finance & Accounting',
            'code' => 'FA',
        ]);
    }

    public function test_cannot_delete_department_with_associated_employees(): void
    {
        $dept = Department::create(['name' => 'Operations', 'code' => 'OPS']);
        $shift = Shift::create(['name' => 'General', 'start_time' => '08:00:00', 'end_time' => '17:00:00']);
        
        $employeeUser = User::factory()->create();
        Employee::create([
            'user_id' => $employeeUser->id,
            'department_id' => $dept->id,
            'nik' => 'EMP-OPS-01',
            'default_shift_id' => $shift->id,
            'join_date' => '2025-01-01',
            'employment_status' => 'permanent',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->admin)->delete(route('admin.departments.destroy', $dept->id));
        $response->assertRedirect();
        $response->assertSessionHas('error');

        $this->assertDatabaseHas('departments', ['id' => $dept->id]);
    }

    public function test_supervisor_is_forbidden_from_managing_departments(): void
    {
        $response = $this->actingAs($this->supervisor)->get(route('admin.departments.index'));
        $response->assertForbidden();
    }
}
