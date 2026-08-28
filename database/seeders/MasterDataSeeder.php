<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\CompanySetting;
use App\Models\Department;
use App\Models\Holiday;
use App\Models\LeaveType;
use App\Models\Position;
use App\Models\Shift;
use Illuminate\Database\Seeder;

class MasterDataSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Branches
        $branchHQ = Branch::firstOrCreate(
            ['code' => 'HQ-JKT'],
            [
                'name' => 'Kantor Pusat Jakarta',
                'address' => 'Jl. Jend. Sudirman Kav. 52-53, Senayan, Kebayoran Baru, Jakarta Selatan',
                'latitude' => -6.22558800,
                'longitude' => 106.80918000,
                'radius_meters' => 150,
                'is_active' => true,
            ]
        );

        $branchBDG = Branch::firstOrCreate(
            ['code' => 'BR-BDG'],
            [
                'name' => 'Kantor Cabang Bandung',
                'address' => 'Jl. Ir. H. Juanda No. 128, Dago, Bandung',
                'latitude' => -6.89063200,
                'longitude' => 107.61665000,
                'radius_meters' => 100,
                'is_active' => true,
            ]
        );

        // 2. Departments
        $deptEng = Department::firstOrCreate(['code' => 'ENG'], ['name' => 'Engineering & IT', 'description' => 'Software and Hardware Infrastructure']);
        $deptHR = Department::firstOrCreate(['code' => 'HRD'], ['name' => 'Human Resources', 'description' => 'People Operations & Talent Acquisition']);
        $deptFin = Department::firstOrCreate(['code' => 'FIN'], ['name' => 'Finance & Accounting', 'description' => 'Financial planning, tax & payroll']);
        $deptOps = Department::firstOrCreate(['code' => 'OPS'], ['name' => 'Operations', 'description' => 'Daily business and logistics operations']);

        // 3. Positions
        Position::firstOrCreate(['department_id' => $deptEng->id, 'name' => 'Head of Engineering', 'level' => 'Manager']);
        Position::firstOrCreate(['department_id' => $deptEng->id, 'name' => 'Senior Software Engineer', 'level' => 'Senior Staff']);
        Position::firstOrCreate(['department_id' => $deptEng->id, 'name' => 'Fullstack Developer', 'level' => 'Staff']);
        Position::firstOrCreate(['department_id' => $deptHR->id, 'name' => 'HR Manager', 'level' => 'Manager']);
        Position::firstOrCreate(['department_id' => $deptHR->id, 'name' => 'People Operations Specialist', 'level' => 'Staff']);
        Position::firstOrCreate(['department_id' => $deptFin->id, 'name' => 'Finance Supervisor', 'level' => 'Supervisor']);
        Position::firstOrCreate(['department_id' => $deptOps->id, 'name' => 'Operations Lead', 'level' => 'Supervisor']);

        // 4. Shifts
        Shift::firstOrCreate(
            ['name' => 'Regular Office (08:00 - 17:00)'],
            [
                'start_time' => '08:00:00',
                'end_time' => '17:00:00',
                'grace_period_minutes' => 15,
                'is_overnight' => false,
                'is_active' => true,
            ]
        );

        Shift::firstOrCreate(
            ['name' => 'Shift Pagi (07:00 - 15:00)'],
            [
                'start_time' => '07:00:00',
                'end_time' => '15:00:00',
                'grace_period_minutes' => 10,
                'is_overnight' => false,
                'is_active' => true,
            ]
        );

        Shift::firstOrCreate(
            ['name' => 'Shift Siang (15:00 - 23:00)'],
            [
                'start_time' => '15:00:00',
                'end_time' => '23:00:00',
                'grace_period_minutes' => 10,
                'is_overnight' => false,
                'is_active' => true,
            ]
        );

        // 5. Leave Types
        LeaveType::firstOrCreate(
            ['code' => 'ANNUAL'],
            [
                'name' => 'Cuti Tahunan',
                'default_quota' => 12,
                'is_paid' => true,
                'requires_attachment' => false,
                'is_active' => true,
            ]
        );

        LeaveType::firstOrCreate(
            ['code' => 'SICK'],
            [
                'name' => 'Izin Sakit',
                'default_quota' => 14,
                'is_paid' => true,
                'requires_attachment' => true,
                'is_active' => true,
            ]
        );

        LeaveType::firstOrCreate(
            ['code' => 'MATERNITY'],
            [
                'name' => 'Cuti Melahirkan',
                'default_quota' => 90,
                'is_paid' => true,
                'requires_attachment' => true,
                'is_active' => true,
            ]
        );

        LeaveType::firstOrCreate(
            ['code' => 'UNPAID'],
            [
                'name' => 'Izin Tanpa Gaji (Unpaid Leave)',
                'default_quota' => 5,
                'is_paid' => false,
                'requires_attachment' => false,
                'is_active' => true,
            ]
        );

        // 6. Holidays
        $currentYear = date('Y');
        Holiday::firstOrCreate(['date' => "{$currentYear}-01-01"], ['name' => 'Tahun Baru Masehi', 'is_national' => true]);
        Holiday::firstOrCreate(['date' => "{$currentYear}-05-01"], ['name' => 'Hari Buruh Internasional', 'is_national' => true]);
        Holiday::firstOrCreate(['date' => "{$currentYear}-08-17"], ['name' => 'Hari Kemerdekaan RI', 'is_national' => true]);
        Holiday::firstOrCreate(['date' => "{$currentYear}-12-25"], ['name' => 'Hari Raya Natal', 'is_national' => true]);

        // 7. Settings
        CompanySetting::set('company_name', 'PT Attention Inovasi Digital', 'Nama Resmi Perusahaan');
        CompanySetting::set('attendance_auto_absent_time', '23:59:00', 'Waktu eksekusi auto-absent di malam hari');
        CompanySetting::set('enable_selfie_verification', '1', 'Wajibkan foto selfie saat clock-in/out');
        CompanySetting::set('enable_gps_restriction', '1', 'Wajibkan verifikasi radius GPS kantor');
    }
}
