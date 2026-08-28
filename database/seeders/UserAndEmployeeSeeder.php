<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\AttendanceCorrection;
use App\Models\Branch;
use App\Models\Department;
use App\Models\Employee;
use App\Models\LeaveBalance;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\OvertimeRequest;
use App\Models\Position;
use App\Models\Shift;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserAndEmployeeSeeder extends Seeder
{
    public function run(): void
    {
        $branchHQ = Branch::where('code', 'HQ-JKT')->first();
        $branchBDG = Branch::where('code', 'BR-BDG')->first() ?? $branchHQ;
        
        $deptEng = Department::where('code', 'ENG')->first();
        $deptHR = Department::where('code', 'HRD')->first();
        $deptFin = Department::where('code', 'FIN')->first();

        // Positions
        $posHeadEng = Position::firstOrCreate(['department_id' => $deptEng->id, 'name' => 'Head of Engineering'], ['level' => 'Manager']);
        $posLeadEng = Position::firstOrCreate(['department_id' => $deptEng->id, 'name' => 'Lead Software Engineer'], ['level' => 'Supervisor']);
        $posStaffEng = Position::firstOrCreate(['department_id' => $deptEng->id, 'name' => 'Fullstack Developer'], ['level' => 'Staff']);

        $posHRManager = Position::firstOrCreate(['department_id' => $deptHR->id, 'name' => 'HR Manager'], ['level' => 'Manager']);
        $posHRSpv = Position::firstOrCreate(['department_id' => $deptHR->id, 'name' => 'HR Operations Supervisor'], ['level' => 'Supervisor']);
        $posStaffHR = Position::firstOrCreate(['department_id' => $deptHR->id, 'name' => 'People Operations Specialist'], ['level' => 'Staff']);

        $posFinStaff = Position::firstOrCreate(['department_id' => $deptFin->id, 'name' => 'Finance & Tax Staff'], ['level' => 'Staff']);

        $shiftReg = Shift::first();
        $leaveTypes = LeaveType::all();
        $annualLeave = LeaveType::where('code', 'ANNUAL')->first();
        $sickLeave = LeaveType::where('code', 'SICK')->first();
        $currentYear = (int) date('Y');

        // =========================================================================
        // 1. SUPER ADMIN (Sistem Administrator - Global Access & Full Settings)
        // =========================================================================
        $adminUser = User::firstOrCreate(
            ['email' => 'admin@attention.test'],
            ['name' => 'System Administrator', 'password' => Hash::make('password')]
        );
        $adminUser->syncRoles(['Super Admin']);

        // =========================================================================
        // 2. HR MANAGER (Sarah Wijaya - Global Access ke Seluruh Divisi)
        // =========================================================================
        $hrUser = User::firstOrCreate(
            ['email' => 'hr@attention.test'],
            ['name' => 'Sarah Wijaya, S.Psi', 'password' => Hash::make('password')]
        );
        $hrUser->syncRoles(['HR Manager']);
        $hrEmp = Employee::firstOrCreate(
            ['user_id' => $hrUser->id],
            [
                'nik' => 'HR001',
                'department_id' => $deptHR->id,
                'position_id' => $posHRManager->id,
                'branch_id' => $branchHQ->id,
                'default_shift_id' => $shiftReg->id,
                'phone' => '081234567890',
                'gender' => 'female',
                'birth_date' => '1992-04-15',
                'join_date' => '2021-01-10',
                'employment_status' => 'permanent',
                'is_active' => true,
            ]
        );
        $this->seedBalances($hrEmp, $leaveTypes, $currentYear);

        // =========================================================================
        // 3. ENGINEERING MANAGER (Ir. Alex Pratama - Department Scoped: Engineering)
        // =========================================================================
        $mgrEngUser = User::firstOrCreate(
            ['email' => 'manager.eng@attention.test'],
            ['name' => 'Ir. Alex Pratama, M.T.', 'password' => Hash::make('password')]
        );
        $mgrEngUser->syncRoles(['Supervisor']);
        $mgrEngEmp = Employee::firstOrCreate(
            ['user_id' => $mgrEngUser->id],
            [
                'nik' => 'ENG-MGR-01',
                'department_id' => $deptEng->id,
                'position_id' => $posHeadEng->id,
                'branch_id' => $branchHQ->id,
                'default_shift_id' => $shiftReg->id,
                'phone' => '081211112222',
                'gender' => 'male',
                'birth_date' => '1987-05-20',
                'join_date' => '2019-01-01',
                'employment_status' => 'permanent',
                'is_active' => true,
            ]
        );
        $this->seedBalances($mgrEngEmp, $leaveTypes, $currentYear);

        // =========================================================================
        // 4. ENGINEERING SUPERVISOR (Hendra Gunawan - Department Scoped: Engineering)
        // =========================================================================
        $spvEngUser = User::firstOrCreate(
            ['email' => 'supervisor.eng@attention.test'],
            ['name' => 'Hendra Gunawan, S.Kom', 'password' => Hash::make('password')]
        );
        // Also ensure legacy supervisor@attention.test works
        $legacySpv = User::firstOrCreate(
            ['email' => 'supervisor@attention.test'],
            ['name' => 'Hendra Gunawan, S.Kom', 'password' => Hash::make('password')]
        );
        $legacySpv->syncRoles(['Supervisor']);

        $spvEngUser->syncRoles(['Supervisor']);
        $spvEngEmp = Employee::firstOrCreate(
            ['user_id' => $spvEngUser->id],
            [
                'nik' => 'ENG-SPV-01',
                'department_id' => $deptEng->id,
                'position_id' => $posLeadEng->id,
                'branch_id' => $branchHQ->id,
                'default_shift_id' => $shiftReg->id,
                'phone' => '081298765432',
                'gender' => 'male',
                'birth_date' => '1989-11-20',
                'join_date' => '2020-03-01',
                'employment_status' => 'permanent',
                'is_active' => true,
            ]
        );
        $this->seedBalances($spvEngEmp, $leaveTypes, $currentYear);

        // =========================================================================
        // 5. HR SUPERVISOR (Maya Kartika - Department Scoped: HR)
        // =========================================================================
        $spvHrUser = User::firstOrCreate(
            ['email' => 'supervisor.hr@attention.test'],
            ['name' => 'Maya Kartika, S.Psi', 'password' => Hash::make('password')]
        );
        $spvHrUser->syncRoles(['Supervisor']);
        $spvHrEmp = Employee::firstOrCreate(
            ['user_id' => $spvHrUser->id],
            [
                'nik' => 'HR-SPV-01',
                'department_id' => $deptHR->id,
                'position_id' => $posHRSpv->id,
                'branch_id' => $branchHQ->id,
                'default_shift_id' => $shiftReg->id,
                'phone' => '081277889900',
                'gender' => 'female',
                'birth_date' => '1991-03-10',
                'join_date' => '2021-05-01',
                'employment_status' => 'permanent',
                'is_active' => true,
            ]
        );
        $this->seedBalances($spvHrEmp, $leaveTypes, $currentYear);

        // =========================================================================
        // 6. STAFF ENGINEERING (Budi Santoso - ESS Portal)
        // =========================================================================
        $staffEngUser = User::firstOrCreate(
            ['email' => 'staff.eng@attention.test'],
            ['name' => 'Budi Santoso (Staff Engineering)', 'password' => Hash::make('password')]
        );
        // Also support budi@attention.test
        $budiLegacy = User::firstOrCreate(
            ['email' => 'budi@attention.test'],
            ['name' => 'Budi Santoso (Staff Engineering)', 'password' => Hash::make('password')]
        );
        $budiLegacy->syncRoles(['Employee']);

        $staffEngUser->syncRoles(['Employee']);
        $staffEngEmp = Employee::firstOrCreate(
            ['user_id' => $staffEngUser->id],
            [
                'nik' => 'ENG-STF-01',
                'department_id' => $deptEng->id,
                'position_id' => $posStaffEng->id,
                'branch_id' => $branchHQ->id,
                'default_shift_id' => $shiftReg->id,
                'phone' => '081311223344',
                'gender' => 'male',
                'birth_date' => '1995-07-08',
                'join_date' => '2022-06-15',
                'employment_status' => 'permanent',
                'is_active' => true,
            ]
        );
        $this->seedBalances($staffEngEmp, $leaveTypes, $currentYear);

        // =========================================================================
        // 7. STAFF HR (Dewi Anggraini - ESS Portal)
        // =========================================================================
        $staffHrUser = User::firstOrCreate(
            ['email' => 'staff.hr@attention.test'],
            ['name' => 'Dewi Anggraini (Staff HR)', 'password' => Hash::make('password')]
        );
        $staffHrUser->syncRoles(['Employee']);
        $staffHrEmp = Employee::firstOrCreate(
            ['user_id' => $staffHrUser->id],
            [
                'nik' => 'HR-STF-01',
                'department_id' => $deptHR->id,
                'position_id' => $posStaffHR->id,
                'branch_id' => $branchHQ->id,
                'default_shift_id' => $shiftReg->id,
                'phone' => '081399887766',
                'gender' => 'female',
                'birth_date' => '1998-12-05',
                'join_date' => '2023-08-01',
                'employment_status' => 'permanent',
                'is_active' => true,
            ]
        );
        $this->seedBalances($staffHrEmp, $leaveTypes, $currentYear);

        // =========================================================================
        // 8. STAFF FINANCE (Andi Wijaya - ESS Portal)
        // =========================================================================
        $staffFinUser = User::firstOrCreate(
            ['email' => 'staff.fin@attention.test'],
            ['name' => 'Andi Wijaya (Staff Finance)', 'password' => Hash::make('password')]
        );
        $staffFinUser->syncRoles(['Employee']);
        $staffFinEmp = Employee::firstOrCreate(
            ['user_id' => $staffFinUser->id],
            [
                'nik' => 'FIN-STF-01',
                'department_id' => $deptFin->id,
                'position_id' => $posFinStaff->id,
                'branch_id' => $branchHQ->id,
                'default_shift_id' => $shiftReg->id,
                'phone' => '081344556677',
                'gender' => 'male',
                'birth_date' => '1996-04-22',
                'join_date' => '2022-09-01',
                'employment_status' => 'permanent',
                'is_active' => true,
            ]
        );
        $this->seedBalances($staffFinEmp, $leaveTypes, $currentYear);

        // =========================================================================
        // 9. INACTIVE EMPLOYEE (Dedi Kusuma - Test Akun Non-Aktif)
        // =========================================================================
        $inactiveUser = User::firstOrCreate(
            ['email' => 'inactive.staff@attention.test'],
            ['name' => 'Dedi Kusuma (Non-Aktif)', 'password' => Hash::make('password')]
        );
        $inactiveUser->syncRoles(['Employee']);
        $inactiveEmp = Employee::firstOrCreate(
            ['user_id' => $inactiveUser->id],
            [
                'nik' => 'ENG-STF-99',
                'department_id' => $deptEng->id,
                'position_id' => $posStaffEng->id,
                'branch_id' => $branchHQ->id,
                'default_shift_id' => $shiftReg->id,
                'phone' => '081999888777',
                'gender' => 'male',
                'birth_date' => '1995-02-14',
                'join_date' => '2022-01-01',
                'employment_status' => 'contract',
                'is_active' => false,
            ]
        );

        // =========================================================================
        // 10. SEED ATTENDANCES FOR PAST DAYS & TODAY
        // =========================================================================
        $allEmployees = [$hrEmp, $mgrEngEmp, $spvEngEmp, $spvHrEmp, $staffEngEmp, $staffHrEmp, $staffFinEmp];
        
        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i)->format('Y-m-d');
            $dayOfWeek = Carbon::parse($date)->dayOfWeek;
            
            // For past history, skip weekends. For today ($i === 0), always seed so demo has data
            if ($i > 0 && ($dayOfWeek === Carbon::SATURDAY || $dayOfWeek === Carbon::SUNDAY)) {
                continue;
            }

            foreach ($allEmployees as $emp) {
                if ($i === 0) {
                    // Today: Present
                    Attendance::firstOrCreate(
                        ['employee_id' => $emp->id, 'date' => $date],
                        [
                            'branch_id' => $emp->branch_id,
                            'shift_id' => $shiftReg->id,
                            'clock_in' => '07:55:00',
                            'in_latitude' => -6.22559000,
                            'in_longitude' => 106.80918500,
                            'in_distance_meters' => 12.5,
                            'status' => 'present',
                            'late_minutes' => 0,
                            'notes' => 'Hadir tepat waktu',
                        ]
                    );
                    continue;
                }

                Attendance::firstOrCreate(
                    ['employee_id' => $emp->id, 'date' => $date],
                    [
                        'branch_id' => $emp->branch_id,
                        'shift_id' => $shiftReg->id,
                        'clock_in' => '07:50:00',
                        'in_latitude' => -6.22558800,
                        'in_longitude' => 106.80918000,
                        'in_distance_meters' => 8.4,
                        'clock_out' => '17:15:00',
                        'out_latitude' => -6.22558800,
                        'out_longitude' => 106.80918000,
                        'out_distance_meters' => 9.1,
                        'status' => 'present',
                        'late_minutes' => 0,
                        'work_duration_minutes' => 565,
                    ]
                );
            }
        }

        // =========================================================================
        // 10. SAMPLE PENDING APPROVAL REQUESTS FOR DEMO COMPARISON
        // =========================================================================
        // 1. Staff Engineering (Budi) submits Leave & Overtime
        LeaveRequest::firstOrCreate(
            ['employee_id' => $staffEngEmp->id, 'start_date' => Carbon::now()->addDays(3)->format('Y-m-d')],
            [
                'leave_type_id' => $annualLeave->id,
                'end_date' => Carbon::now()->addDays(4)->format('Y-m-d'),
                'total_days' => 2,
                'reason' => '[Engineering] Cuti Tahunan: Family Gathering',
                'status' => 'pending',
            ]
        );

        OvertimeRequest::firstOrCreate(
            ['employee_id' => $staffEngEmp->id, 'date' => Carbon::now()->format('Y-m-d')],
            [
                'start_time' => '18:00:00',
                'end_time' => '21:00:00',
                'duration_hours' => 3.0,
                'task_description' => '[Engineering] Lembur: Deployment Production & Migrasi Database',
                'status' => 'pending',
            ]
        );

        // 2. Staff HR (Dewi) submits Sick Leave
        LeaveRequest::firstOrCreate(
            ['employee_id' => $staffHrEmp->id, 'start_date' => Carbon::now()->addDays(2)->format('Y-m-d')],
            [
                'leave_type_id' => $sickLeave->id,
                'end_date' => Carbon::now()->addDays(2)->format('Y-m-d'),
                'total_days' => 1,
                'reason' => '[HRD] Izin Sakit: Rawat Jalan Kontrol Kesehatan',
                'status' => 'pending',
            ]
        );

        // 3. Staff Finance (Andi) submits Overtime
        OvertimeRequest::firstOrCreate(
            ['employee_id' => $staffFinEmp->id, 'date' => Carbon::now()->format('Y-m-d')],
            [
                'start_time' => '18:00:00',
                'end_time' => '20:30:00',
                'duration_hours' => 2.5,
                'task_description' => '[Finance] Lembur: Rekonsiliasi Laporan SPT Masa Pajak',
                'status' => 'pending',
            ]
        );

        // =========================================================================
        // 11. SAMPLE INITIAL ACTIVITY LOGS (AUDIT TRAIL)
        // =========================================================================
        \App\Models\ActivityLog::firstOrCreate(
            ['description' => 'Menambahkan master shift baru: Regular Office (08:00 - 17:00)'],
            [
                'user_id' => $adminUser->id,
                'user_name' => $adminUser->name,
                'user_role' => 'Super Admin',
                'module' => 'Master Shift',
                'action' => 'CREATE',
                'ip_address' => '192.168.1.10',
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/128.0.0.0',
                'created_at' => Carbon::now()->subDays(3),
            ]
        );

        \App\Models\ActivityLog::firstOrCreate(
            ['description' => 'Menambahkan lokasi kantor baru: Kantor Pusat Jakarta (HQ-JKT) - Radius: 150m'],
            [
                'user_id' => $hrUser->id,
                'user_name' => $hrUser->name,
                'user_role' => 'HR Manager',
                'module' => 'Master Kantor',
                'action' => 'CREATE',
                'ip_address' => '192.168.1.15',
                'user_agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7)',
                'created_at' => Carbon::now()->subDays(2),
            ]
        );

        \App\Models\ActivityLog::firstOrCreate(
            ['description' => 'Menambahkan divisi baru: Engineering & IT (ENG)'],
            [
                'user_id' => $hrUser->id,
                'user_name' => $hrUser->name,
                'user_role' => 'HR Manager',
                'module' => 'Master Divisi',
                'action' => 'CREATE',
                'ip_address' => '192.168.1.15',
                'user_agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7)',
                'created_at' => Carbon::now()->subDays(2),
            ]
        );

        \App\Models\ActivityLog::firstOrCreate(
            ['description' => 'Menambahkan karyawan baru: Budi Santoso (NIK: ENG-STF-01, Role: Employee)'],
            [
                'user_id' => $hrUser->id,
                'user_name' => $hrUser->name,
                'user_role' => 'HR Manager',
                'module' => 'Data Karyawan',
                'action' => 'CREATE',
                'ip_address' => '192.168.1.15',
                'user_agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7)',
                'created_at' => Carbon::now()->subDays(1),
            ]
        );

        \App\Models\ActivityLog::firstOrCreate(
            ['description' => 'Menyetujui pengajuan lembur Budi Santoso (3.0 Jam pada ' . Carbon::now()->format('d/m/Y') . ')'],
            [
                'user_id' => $spvEngUser->id,
                'user_name' => $spvEngUser->name,
                'user_role' => 'Supervisor',
                'module' => 'Persetujuan Lembur',
                'action' => 'APPROVE',
                'properties' => [
                    'deskripsi' => 'Pengajuan lembur Budi Santoso (3.0 Jam) untuk tugas Deployment Production & Migrasi Database disetujui.',
                ],
                'ip_address' => '192.168.1.22',
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
                'created_at' => Carbon::now()->subHours(4),
            ]
        );

        \App\Models\ActivityLog::firstOrCreate(
            ['description' => 'Menolak permohonan Cuti Tahunan untuk Dewi Lestari'],
            [
                'user_id' => $spvHrUser->id,
                'user_name' => $spvHrUser->name,
                'user_role' => 'Supervisor',
                'module' => 'Persetujuan Cuti',
                'action' => 'REJECT',
                'properties' => [
                    'alasan_penolakan' => 'Kuota personil tim HRD sedang terbatas karena agenda rekrutmen massal. Harap ajukan di minggu berikutnya.',
                ],
                'ip_address' => '192.168.1.25',
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
                'created_at' => Carbon::now()->subHours(2),
            ]
        );
    }

    private function seedBalances(Employee $employee, $leaveTypes, int $year): void
    {
        foreach ($leaveTypes as $lt) {
            LeaveBalance::firstOrCreate(
                ['employee_id' => $employee->id, 'leave_type_id' => $lt->id, 'year' => $year],
                ['quota' => $lt->default_quota, 'used' => 0, 'remaining' => $lt->default_quota]
            );
        }
    }
}
