<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Position;
use App\Models\Shift;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class EmployeePortalTest extends TestCase
{
    use RefreshDatabase;

    private User $employeeUser;

    protected function setUp(): void
    {
        parent::setUp();

        $roleEmp = Role::create(['name' => 'Employee']);
        $this->employeeUser = User::factory()->create(['name' => 'Budi Santoso']);
        $this->employeeUser->assignRole($roleEmp);

        $branch = Branch::create([
            'name' => 'HQ Jakarta',
            'code' => 'HQ-01',
            'latitude' => -6.2,
            'longitude' => 106.8,
            'radius_meters' => 100,
            'is_active' => true,
        ]);

        $dept = Department::create([
            'name' => 'Engineering',
            'code' => 'ENG',
        ]);

        $pos = Position::create([
            'department_id' => $dept->id,
            'name' => 'Software Engineer',
        ]);

        $shift = Shift::create([
            'name' => 'Regular',
            'start_time' => '08:00:00',
            'end_time' => '17:00:00',
            'is_active' => true,
        ]);

        Employee::create([
            'user_id' => $this->employeeUser->id,
            'nik' => 'EMP001',
            'department_id' => $dept->id,
            'position_id' => $pos->id,
            'branch_id' => $branch->id,
            'default_shift_id' => $shift->id,
            'join_date' => '2025-01-01',
            'employment_status' => 'permanent',
            'is_active' => true,
        ]);
    }

    public function test_leave_page_contains_back_button_to_dashboard(): void
    {
        $response = $this->actingAs($this->employeeUser)->get(route('employee.leave.index'));
        $response->assertSuccessful();
        $response->assertSee(route('employee.dashboard'));
        $response->assertSee('Kembali');
    }

    public function test_overtime_page_contains_back_button_to_dashboard(): void
    {
        $response = $this->actingAs($this->employeeUser)->get(route('employee.overtime.index'));
        $response->assertSuccessful();
        $response->assertSee(route('employee.dashboard'));
        $response->assertSee('Kembali');
    }

    public function test_attendance_history_page_contains_back_button_to_dashboard(): void
    {
        $response = $this->actingAs($this->employeeUser)->get(route('employee.attendance.history'));
        $response->assertSuccessful();
        $response->assertSee(route('employee.dashboard'));
        $response->assertSee('Kembali');
    }

    public function test_employee_sees_approver_and_rejection_reason_for_leave(): void
    {
        $reviewer = User::factory()->create(['name' => 'Pak Hendra (Manager)']);
        $leaveType = \App\Models\LeaveType::create(['name' => 'Cuti Tahunan', 'code' => 'CT', 'default_quota' => 12]);

        $employee = $this->employeeUser->employee;

        // Rejected leave request
        \App\Models\LeaveRequest::create([
            'employee_id' => $employee->id,
            'leave_type_id' => $leaveType->id,
            'start_date' => '2026-09-01',
            'end_date' => '2026-09-02',
            'total_days' => 2,
            'reason' => 'Keperluan keluarga mendadak',
            'status' => 'rejected',
            'approved_by' => $reviewer->id,
            'rejection_reason' => 'Jadwal sprint release tidak dapat ditinggal.',
        ]);

        $response = $this->actingAs($this->employeeUser)->get(route('employee.leave.index'));
        $response->assertSuccessful();
        $response->assertSee('Ditolak');
        $response->assertSee('Pak Hendra (Manager)');
        $response->assertSee('Alasan Penolakan:');
        $response->assertSee('Jadwal sprint release tidak dapat ditinggal.');
    }

    public function test_employee_sees_approver_and_rejection_reason_for_overtime(): void
    {
        $reviewer = User::factory()->create(['name' => 'Bu Sarah (HR)']);
        $employee = $this->employeeUser->employee;

        // Approved overtime
        \App\Models\OvertimeRequest::create([
            'employee_id' => $employee->id,
            'date' => '2026-08-28',
            'start_time' => '18:00:00',
            'end_time' => '20:00:00',
            'duration_hours' => 2.0,
            'task_description' => 'Fixing production issue',
            'status' => 'approved',
            'approved_by' => $reviewer->id,
        ]);

        $response = $this->actingAs($this->employeeUser)->get(route('employee.overtime.index'));
        $response->assertSuccessful();
        $response->assertSee('Disetujui');
        $response->assertSee('Bu Sarah (HR)');
    }
}
