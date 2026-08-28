@extends('layouts.admin', ['title' => 'Dashboard Overview', 'header' => 'Dashboard Utama'])

@section('content')
<div class="space-y-6">

    @if(isset($supervisorDepartment) && $supervisorDepartment)
        <x-radix-alert type="info" title="Akses Supervisor Divisi: {{ $supervisorDepartment->name }}">
            Anda sedang mengelola data presensi dan permohonan khusus untuk anggota divisi <strong>{{ $supervisorDepartment->name }} ({{ $supervisorDepartment->code ?? '-' }})</strong>.
        </x-radix-alert>
    @endif

    <!-- 1. Hero KPI Data Cards (Attention OS Specification) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        
        <!-- Total Karyawan -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Total Karyawan</span>
                <p class="text-3xl font-black text-slate-900 mt-1">{{ $totalEmployees }}</p>
                <span class="text-[11px] text-slate-500 font-medium mt-1 block">Aktif di {{ $totalBranches }} Lokasi Kantor</span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
            </div>
        </div>

        <!-- Hadir Tepat Waktu -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs font-bold text-emerald-600 uppercase tracking-wider block">Hadir Tepat Waktu</span>
                <p class="text-3xl font-black text-slate-900 mt-1">{{ $presentCount }}</p>
                <span class="text-[11px] text-emerald-600 font-medium mt-1 block">{{ $totalEmployees > 0 ? round(($presentCount / $totalEmployees) * 100) : 0 }}% dari total staf</span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
        </div>

        <!-- Terlambat -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs font-bold text-amber-600 uppercase tracking-wider block">Terlambat Hadir</span>
                <p class="text-3xl font-black text-slate-900 mt-1">{{ $lateCount }}</p>
                <span class="text-[11px] text-amber-600 font-medium mt-1 block">Melewati batas toleransi</span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
        </div>

        <!-- Menunggu Persetujuan -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs font-bold text-indigo-600 uppercase tracking-wider block">Persetujuan Tertunda</span>
                <p class="text-3xl font-black text-slate-900 mt-1">{{ $totalPendingApprovals }}</p>
                <a href="{{ route('admin.approvals.index') }}" class="text-[11px] text-blue-600 hover:underline font-semibold mt-1 block" aria-label="Review persetujuan tertunda sekarang">Review sekarang &rarr;</a>
            </div>
            <div class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>
            </div>
        </div>

    </div>

    <!-- 2. Main Live Feed & Quick Info -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Live Today Attendance Feed (2 Cols) -->
        <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden flex flex-col justify-between">
            <div>
                <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                    <div>
                        <h2 class="font-bold text-slate-900 text-sm">Aktivitas Presensi Hari Ini</h2>
                        <p class="text-xs text-slate-500 mt-0.5">Tanggal: {{ Carbon\Carbon::parse($today)->translatedFormat('l, d F Y') }}</p>
                    </div>
                    <a href="{{ route('admin.attendance.index', ['date' => $today]) }}" class="text-xs font-semibold text-blue-600 hover:underline" aria-label="Lihat seluruh log aktivitas presensi hari ini">
                        Lihat Seluruh Log &rarr;
                    </a>
                </div>

                @if($recentAttendances->isEmpty())
                    <div class="p-10 text-center text-xs text-slate-400">
                        Belum ada data presensi yang masuk untuk hari ini.
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-slate-50 text-slate-600 font-semibold border-b border-slate-200/80">
                                <tr>
                                    <th scope="col" class="px-4 py-3">Karyawan</th>
                                    <th scope="col" class="px-4 py-3">Lokasi / Cabang</th>
                                    <th scope="col" class="px-4 py-3">Clock In</th>
                                    <th scope="col" class="px-4 py-3">Clock Out</th>
                                    <th scope="col" class="px-4 py-3">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach($recentAttendances as $att)
                                    <tr class="hover:bg-slate-50/60 transition">
                                        <td class="px-4 py-3">
                                            <div class="flex items-center space-x-2.5">
                                                <img src="{{ $att->employee?->avatar_url ?? \App\Models\Employee::generateInitialSvg($att->employee?->user?->name ?? 'User') }}" 
                                                     alt="Foto {{ $att->employee?->user?->name ?? 'Karyawan' }}"
                                                     class="w-7 h-7 rounded-full object-cover">
                                                <div>
                                                    <p class="font-bold text-slate-900">{{ $att->employee?->user?->name ?? 'Karyawan' }}</p>
                                                    <p class="text-[10px] text-slate-500 font-mono">{{ $att->employee?->nik ?? '-' }} &bull; {{ $att->employee?->department?->name ?? '-' }}</p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3 text-slate-600">
                                            {{ $att->branch?->name ?? 'Kantor Pusat' }}
                                        </td>
                                        <td class="px-4 py-3 font-mono font-semibold text-slate-800">
                                            {{ $att->clock_in ? Carbon\Carbon::parse($att->clock_in)->format('H:i') : '--:--' }}
                                        </td>
                                        <td class="px-4 py-3 font-mono font-semibold text-slate-800">
                                            {{ $att->clock_out ? Carbon\Carbon::parse($att->clock_out)->format('H:i') : '--:--' }}
                                        </td>
                                        <td class="px-4 py-3">
                                            @if($att->status === 'present')
                                                <span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-emerald-100 text-emerald-800">Hadir</span>
                                            @elseif($att->status === 'late')
                                                <span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-amber-100 text-amber-800">Telat ({{ $att->late_minutes }}m)</span>
                                            @elseif($att->status === 'leave')
                                                <span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-blue-100 text-blue-800">Cuti</span>
                                            @elseif($att->status === 'sick')
                                                <span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-purple-100 text-purple-800">Sakit</span>
                                            @else
                                                <span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-slate-100 text-slate-700">{{ strtoupper($att->status) }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
            <div class="p-3 bg-slate-50 border-t border-slate-100 text-right">
                <a href="{{ route('admin.attendance.create') }}" class="text-xs font-semibold text-blue-600 hover:underline">
                    + Tambah Presensi Manual
                </a>
            </div>
        </div>

        <!-- Quick Summary & Actions (1 Col) -->
        <div class="space-y-4">
            
            <!-- Quick Actions Card -->
            <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-sm space-y-3">
                <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider">Aksi Cepat</h3>
                
                <a href="{{ route('admin.employees.create') }}" class="w-full flex items-center justify-between p-3 bg-slate-50 hover:bg-blue-50 border border-slate-200/80 hover:border-blue-300 rounded-xl transition group">
                    <div class="flex items-center space-x-3">
                        <div class="w-8 h-8 rounded-lg bg-blue-600 text-white flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path></svg>
                        </div>
                        <span class="text-xs font-bold text-slate-800 group-hover:text-blue-700">Tambah Karyawan Baru</span>
                    </div>
                    <svg class="w-4 h-4 text-slate-400 group-hover:text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                </a>

                <a href="{{ route('admin.reports.index') }}" class="w-full flex items-center justify-between p-3 bg-slate-50 hover:bg-blue-50 border border-slate-200/80 hover:border-blue-300 rounded-xl transition group">
                    <div class="flex items-center space-x-3">
                        <div class="w-8 h-8 rounded-lg bg-emerald-600 text-white flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        </div>
                        <span class="text-xs font-bold text-slate-800 group-hover:text-blue-700">Export Laporan Payroll</span>
                    </div>
                    <svg class="w-4 h-4 text-slate-400 group-hover:text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                </a>

                <a href="{{ route('admin.branches.create') }}" class="w-full flex items-center justify-between p-3 bg-slate-50 hover:bg-blue-50 border border-slate-200/80 hover:border-blue-300 rounded-xl transition group">
                    <div class="flex items-center space-x-3">
                        <div class="w-8 h-8 rounded-lg bg-indigo-600 text-white flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path></svg>
                        </div>
                        <span class="text-xs font-bold text-slate-800 group-hover:text-blue-700">Setting Lokasi & Geofence</span>
                    </div>
                    <svg class="w-4 h-4 text-slate-400 group-hover:text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                </a>
            </div>

            <!-- Branch Status Summary -->
            <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-sm">
                <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider mb-3">Cabang / Geofence</h3>
                <div class="space-y-2">
                    @foreach($branches as $br)
                        <div class="p-3 bg-slate-50 rounded-xl border border-slate-200/60 flex items-center justify-between">
                            <div>
                                <p class="text-xs font-bold text-slate-900">{{ $br->name }}</p>
                                <p class="text-[10px] text-slate-500 font-mono">{{ $br->code }} &bull; Radius: {{ $br->radius_meters }}m</p>
                            </div>
                            <span class="px-2 py-0.5 text-[9px] font-bold uppercase rounded bg-emerald-100 text-emerald-800">Aktif</span>
                        </div>
                    @endforeach
                </div>
            </div>

        </div>

    </div>

</div>
@endsection
