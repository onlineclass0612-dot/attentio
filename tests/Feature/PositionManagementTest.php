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

class PositionManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $hrManager;
    private User $supervisor;
    private Department $dept;

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

        $this->dept = Department::create(['name' => 'Engineering', 'code' => 'ENG']);
    }

    public function test_admin_can_view_positions_index(): void
    {
        Position::create([
            'department_id' => $this->dept->id,
            'name' => 'Senior Backend Engineer',
            'level' => 'Staff',
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.positions.index'));
        $response->assertSuccessful();
        $response->assertSee('Master Jabatan (Positions)');
        $response->assertSee('Senior Backend Engineer');
    }

    public function test_hr_manager_can_create_new_position(): void
    {
        $payload = [
            'department_id' => $this->dept->id,
            'name' => 'Lead DevOps Engineer',
            'level' => 'Supervisor',
        ];

        $response = $this->actingAs($this->hrManager)->post(route('admin.positions.store'), $payload);
        $response->assertRedirect(route('admin.positions.index'));

        $this->assertDatabaseHas('positions', [
            'department_id' => $this->dept->id,
            'name' => 'Lead DevOps Engineer',
            'level' => 'Supervisor',
        ]);
    }

    public function test_admin_can_update_position(): void
    {
        $pos = Position::create([
            'department_id' => $this->dept->id,
            'name' => 'Frontend Dev',
            'level' => 'Staff',
        ]);

        $payload = [
            'department_id' => $this->dept->id,
            'name' => 'Principal Frontend Architect',
            'level' => 'Manager',
        ];

        $response = $this->actingAs($this->admin)->put(route('admin.positions.update', $pos->id), $payload);
        $response->assertRedirect(route('admin.positions.index'));

        $this->assertDatabaseHas('positions', [
            'id' => $pos->id,
            'name' => 'Principal Frontend Architect',
            'level' => 'Manager',
        ]);
    }

    public function test_cannot_delete_position_with_associated_employees(): void
    {
        $pos = Position::create([
            'department_id' => $this->dept->id,
            'name' => 'QA Specialist',
            'level' => 'Staff',
        ]);

        $shift = Shift::create(['name' => 'General', 'start_time' => '08:00:00', 'end_time' => '17:00:00']);
        $employeeUser = User::factory()->create();
        Employee::create([
            'user_id' => $employeeUser->id,
            'department_id' => $this->dept->id,
            'position_id' => $pos->id,
            'nik' => 'EMP-QA-01',
            'default_shift_id' => $shift->id,
            'join_date' => '2025-01-01',
            'employment_status' => 'permanent',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->admin)->delete(route('admin.positions.destroy', $pos->id));
        $response->assertRedirect();
        $response->assertSessionHas('error');

        $this->assertDatabaseHas('positions', ['id' => $pos->id]);
    }

    public function test_supervisor_is_forbidden_from_managing_positions(): void
    {
        $response = $this->actingAs($this->supervisor)->get(route('admin.positions.index'));
        $response->assertForbidden();
    }
}
