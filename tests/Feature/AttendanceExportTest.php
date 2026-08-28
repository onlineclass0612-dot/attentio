<?php

namespace Tests\Feature;

use App\Exports\AttendanceReportExport;
use App\Models\Branch;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Position;
use App\Models\Shift;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Maatwebsite\Excel\Facades\Excel;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AttendanceExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_download_excel_report(): void
    {
        $role = Role::create(['name' => 'Super Admin']);
        $user = User::factory()->create();
        $user->assignRole($role);

        Excel::fake();

        $response = $this->actingAs($user)->get(route('admin.reports.excel', [
            'start_date' => Carbon::now()->startOfMonth()->format('Y-m-d'),
            'end_date' => Carbon::now()->format('Y-m-d'),
        ]));

        $response->assertSuccessful();
        Excel::assertDownloaded('Rekap_Presensi_' . Carbon::now()->startOfMonth()->format('Y-m-d') . '_sampai_' . Carbon::now()->format('Y-m-d') . '.xlsx', function (AttendanceReportExport $export) {
            return true;
        });
    }

    public function test_attendance_report_export_collection_and_mapping(): void
    {
        $role = Role::create(['name' => 'Super Admin']);
        $user = User::factory()->create(['name' => 'John Doe']);
        $user->assignRole($role);

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

        $employee = Employee::create([
            'user_id' => $user->id,
            'nik' => 'EMP001',
            'department_id' => $dept->id,
            'position_id' => $pos->id,
            'branch_id' => $branch->id,
            'default_shift_id' => $shift->id,
            'join_date' => '2025-01-01',
            'employment_status' => 'permanent',
            'is_active' => true,
        ]);

        \App\Models\Attendance::create([
            'employee_id' => $employee->id,
            'branch_id' => $branch->id,
            'shift_id' => $shift->id,
            'date' => Carbon::today()->format('Y-m-d'),
            'clock_in' => '08:05:00',
            'clock_out' => '17:00:00',
            'status' => 'late',
            'late_minutes' => 5,
            'early_leave_minutes' => 0,
            'work_duration_minutes' => 535,
            'notes' => 'Traffic',
        ]);

        $export = new AttendanceReportExport(
            Carbon::today()->startOfMonth()->format('Y-m-d'),
            Carbon::today()->format('Y-m-d'),
            $dept->id,
            $branch->id
        );

        $collection = $export->collection();
        $this->assertCount(1, $collection);

        $row = $collection->first();
        $mapped = $export->map($row);

        $this->assertEquals(Carbon::today()->format('d/m/Y'), $mapped[0]);
        $this->assertEquals('EMP001', $mapped[1]);
        $this->assertEquals('John Doe', $mapped[2]);
        $this->assertEquals('Engineering', $mapped[3]);
        $this->assertEquals('Software Engineer', $mapped[4]);
        $this->assertEquals('HQ Jakarta', $mapped[5]);
        $this->assertEquals('08:05:00', $mapped[6]);
        $this->assertEquals('17:00:00', $mapped[7]);
        $this->assertEquals('LATE', $mapped[8]);
        $this->assertEquals(5, $mapped[9]);
        $this->assertEquals(0, $mapped[10]);
        $this->assertEquals(535, $mapped[11]);
        $this->assertEquals('Traffic', $mapped[12]);
    }

    public function test_admin_can_view_reports_index_page(): void
    {
        $role = Role::create(['name' => 'Super Admin']);
        $user = User::factory()->create();
        $user->assignRole($role);

        $response = $this->actingAs($user)->get(route('admin.reports.index'));
        $response->assertSuccessful();
        $response->assertSee('Laporan Kehadiran Karyawan');
        $response->assertSee('Tampilkan Rekap');
    }

    public function test_supervisor_can_view_reports_index_page(): void
    {
        $spvRole = Role::create(['name' => 'Supervisor']);
        $dept = Department::create(['name' => 'Engineering', 'code' => 'ENG']);
        $branch = Branch::create(['name' => 'HQ', 'code' => 'HQ-01', 'latitude' => -6.2, 'longitude' => 106.8, 'radius_meters' => 100, 'is_active' => true]);
        $shift = Shift::create(['name' => 'General', 'start_time' => '08:00:00', 'end_time' => '17:00:00', 'is_active' => true]);

        $user = User::factory()->create(['name' => 'Spv Lead']);
        $user->assignRole($spvRole);
        Employee::create([
            'user_id' => $user->id,
            'department_id' => $dept->id,
            'branch_id' => $branch->id,
            'default_shift_id' => $shift->id,
            'nik' => 'SPV001',
            'join_date' => '2024-01-01',
            'employment_status' => 'permanent',
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->get(route('admin.reports.index'));
        $response->assertSuccessful();
        $response->assertSee('Laporan Kehadiran Karyawan');
        $response->assertSee('Rekapitulasi Khusus Divisi: Engineering');
    }
}
