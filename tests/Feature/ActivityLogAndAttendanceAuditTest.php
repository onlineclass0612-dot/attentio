<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\Attendance;
use App\Models\Branch;
use App\Models\Department;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\Position;
use App\Models\Shift;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ActivityLogAndAttendanceAuditTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $hrManager;
    private User $supervisor;
    private User $employeeUser;
    private Employee $employee;
    private Department $dept;
    private LeaveType $leaveType;

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

        $this->dept = Department::create(['name' => 'Engineering', 'code' => 'ENG']);
        $pos = Position::create(['department_id' => $this->dept->id, 'name' => 'Software Engineer', 'level' => 'Staff']);

        // 1. Super Admin
        $this->admin = User::factory()->create(['name' => 'Admin Boss']);
        $this->admin->assignRole($superAdminRole);

        // 2. HR Manager
        $this->hrManager = User::factory()->create(['name' => 'Sarah HR']);
        $this->hrManager->assignRole($hrRole);

        // 3. Supervisor
        $this->supervisor = User::factory()->create(['name' => 'Hendra Spv']);
        $this->supervisor->assignRole($spvRole);
        Employee::create([
            'user_id' => $this->supervisor->id,
            'department_id' => $this->dept->id,
            'branch_id' => $branch->id,
            'default_shift_id' => $shift->id,
            'nik' => 'SPV001',
            'join_date' => '2024-01-01',
            'employment_status' => 'permanent',
            'is_active' => true,
        ]);

        // 4. Employee
        $this->employeeUser = User::factory()->create(['name' => 'Budi Staff']);
        $this->employeeUser->assignRole($empRole);
        $this->employee = Employee::create([
            'user_id' => $this->employeeUser->id,
            'department_id' => $this->dept->id,
            'position_id' => $pos->id,
            'branch_id' => $branch->id,
            'default_shift_id' => $shift->id,
            'nik' => 'EMP001',
            'join_date' => '2024-01-01',
            'employment_status' => 'permanent',
            'is_active' => true,
        ]);
    }

    public function test_only_super_admin_can_access_activity_logs(): void
    {
        // Super Admin can view
        $response = $this->actingAs($this->admin)->get(route('admin.activity_logs.index'));
        $response->assertSuccessful();
        $response->assertSee('Log Aktivitas & Audit Trail');

        // HR Manager is forbidden
        $responseHr = $this->actingAs($this->hrManager)->get(route('admin.activity_logs.index'));
        $responseHr->assertForbidden();

        // Supervisor is forbidden
        $responseSpv = $this->actingAs($this->supervisor)->get(route('admin.activity_logs.index'));
        $responseSpv->assertForbidden();
    }

    public function test_activity_log_is_recorded_when_department_is_created(): void
    {
        $response = $this->actingAs($this->hrManager)->post(route('admin.departments.store'), [
            'name' => 'Digital Marketing',
            'code' => 'MKT',
            'description' => 'Pemasaran online',
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('activity_logs', [
            'module' => 'Master Divisi',
            'action' => 'CREATE',
            'user_name' => 'Sarah HR',
            'user_role' => 'HR Manager',
        ]);
    }

    public function test_activity_log_is_recorded_when_leave_is_approved(): void
    {
        $leave = LeaveRequest::create([
            'employee_id' => $this->employee->id,
            'leave_type_id' => $this->leaveType->id,
            'start_date' => Carbon::now()->addDays(2)->format('Y-m-d'),
            'end_date' => Carbon::now()->addDays(3)->format('Y-m-d'),
            'total_days' => 2,
            'reason' => 'Liburan',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->supervisor)->post(route('admin.approvals.leave.approve', $leave->id));
        $response->assertRedirect();

        $this->assertDatabaseHas('activity_logs', [
            'module' => 'Persetujuan Cuti',
            'action' => 'APPROVE',
            'user_name' => 'Hendra Spv',
        ]);
    }

    public function test_attendance_cannot_be_deleted(): void
    {
        $branch = Branch::first();
        $shift = Shift::first();

        $att = Attendance::create([
            'employee_id' => $this->employee->id,
            'date' => Carbon::today()->format('Y-m-d'),
            'branch_id' => $branch->id,
            'shift_id' => $shift->id,
            'clock_in' => '08:00:00',
            'status' => 'present',
        ]);

        // Attempting to delete attendance
        $response = $this->actingAs($this->admin)->delete('/admin/attendance/' . $att->id);
        
        // Route is disabled and returns 404 / 405 Method Not Allowed
        $this->assertTrue(in_array($response->status(), [404, 405, 302]));

        // Attendance record still exists in database
        $this->assertDatabaseHas('attendances', ['id' => $att->id]);
    }

    public function test_activity_log_records_diff_when_employee_is_updated(): void
    {
        $newDept = Department::create(['name' => 'Finance', 'code' => 'FIN']);
        $newPos = Position::create(['department_id' => $newDept->id, 'name' => 'Finance Staff', 'level' => 'Staff']);

        $response = $this->actingAs($this->admin)->put(route('admin.employees.update', $this->employee->id), [
            'name' => 'Budi Staff Updated',
            'email' => $this->employeeUser->email,
            'nik' => $this->employee->nik,
            'role' => 'Employee',
            'department_id' => $newDept->id,
            'position_id' => $newPos->id,
            'branch_id' => $this->employee->branch_id,
            'default_shift_id' => $this->employee->default_shift_id,
            'phone' => '081234567890',
            'gender' => 'male',
            'employment_status' => 'contract',
            'join_date' => '2024-01-01',
            'is_active' => '1',
        ]);

        $response->assertRedirect(route('admin.employees.index'));

        $log = ActivityLog::where('module', 'Data Karyawan')
            ->where('action', 'UPDATE')
            ->latest('id')
            ->first();

        $this->assertNotNull($log);
        $this->assertStringContainsString('Budi Staff Updated', $log->description);
        $this->assertStringContainsString('Perubahan', $log->description);

        $props = $log->properties;
        $this->assertIsArray($props);
        $this->assertArrayHasKey('sebelum', $props);
        $this->assertArrayHasKey('sesudah', $props);
        $this->assertEquals('Engineering', $props['sebelum']['divisi']);
        $this->assertEquals('Finance', $props['sesudah']['divisi']);
        $this->assertEquals('Pegawai Tetap (Permanent)', $props['sebelum']['status_kepegawaian']);
        $this->assertEquals('Pegawai Kontrak (Contract)', $props['sesudah']['status_kepegawaian']);
    }
}
