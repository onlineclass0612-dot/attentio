@extends('layouts.app')

@section('content')
<div class="space-y-6">

    <!-- 1. Hero Greeting & Live Clock Card -->
    <div class="bg-gradient-to-br from-blue-600 to-indigo-700 rounded-2xl p-6 text-white shadow-xl shadow-blue-600/20 relative overflow-hidden">
        <div class="absolute -right-10 -bottom-10 w-44 h-44 bg-white/10 rounded-full blur-2xl pointer-events-none"></div>
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <span class="inline-block px-2.5 py-1 text-xs font-semibold bg-white/20 backdrop-blur-sm rounded-full mb-2">
                    {{ Carbon\Carbon::now()->translatedFormat('l, d F Y') }}
                </span>
                <h1 class="text-2xl font-bold tracking-tight">Halo, {{ explode(' ', $employee->user->name)[0] }}! 👋</h1>
                <p class="text-blue-100 text-xs mt-1">
                    {{ $employee->position->name ?? 'Karyawan' }} &bull; {{ $employee->department->name ?? 'Departemen' }}
                </p>
            </div>

            <!-- Live Clock Widget -->
            <div class="text-left md:text-right bg-black/20 backdrop-blur-md px-4 py-3 rounded-xl border border-white/10" x-data="{ time: '' }" x-init="setInterval(() => time = new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' }), 1000)">
                <span class="text-[10px] text-blue-200 uppercase font-semibold tracking-wider block">Waktu Saat Ini</span>
                <span class="text-2xl font-black font-mono tracking-wider" x-text="time || '{{ Carbon\Carbon::now()->format('H:i:s') }}'"></span>
                <span class="text-[10px] text-blue-200 block">WIB (GMT+7)</span>
            </div>
        </div>
    </div>

    <!-- 2. Today's Attendance Status & Quick Action Card -->
    <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-sm">
        <div class="flex items-center justify-between mb-4">
            <h2 class="font-bold text-slate-900 text-sm flex items-center">
                <svg class="w-4 h-4 text-blue-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                Status Presensi Hari Ini
            </h2>
            <span class="text-xs text-slate-500 font-medium">
                Kantor: <strong class="text-slate-800">{{ $assignedBranch->name ?? 'Kantor Pusat' }}</strong>
            </span>
        </div>

        <div class="grid grid-cols-2 gap-3 mb-4">
            <!-- Clock In Status -->
            <div class="p-3.5 bg-slate-50 rounded-xl border border-slate-200/60">
                <span class="text-[10px] uppercase font-bold text-slate-400 block tracking-wider">Clock In (Masuk)</span>
                <div class="mt-1 flex items-baseline space-x-2">
                    <span class="text-lg font-black text-slate-900 font-mono">
                        {{ $attendanceToday && $attendanceToday->clock_in ? Carbon\Carbon::parse($attendanceToday->clock_in)->format('H:i') : '--:--' }}
                    </span>
                    @if($attendanceToday && $attendanceToday->clock_in)
                        <span class="px-2 py-0.5 text-[10px] font-bold rounded-full {{ $attendanceToday->status === 'late' ? 'bg-amber-100 text-amber-800' : 'bg-emerald-100 text-emerald-800' }}">
                            {{ $attendanceToday->status === 'late' ? 'Terlambat' : 'Tepat Waktu' }}
                        </span>
                    @endif
                </div>
            </div>

            <!-- Clock Out Status -->
            <div class="p-3.5 bg-slate-50 rounded-xl border border-slate-200/60">
                <span class="text-[10px] uppercase font-bold text-slate-400 block tracking-wider">Clock Out (Pulang)</span>
                <div class="mt-1 flex items-baseline space-x-2">
                    <span class="text-lg font-black text-slate-900 font-mono">
                        {{ $attendanceToday && $attendanceToday->clock_out ? Carbon\Carbon::parse($attendanceToday->clock_out)->format('H:i') : '--:--' }}
                    </span>
                    @if($attendanceToday && $attendanceToday->clock_out)
                        <span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-blue-100 text-blue-800">
                            Selesai
                        </span>
                    @endif
                </div>
            </div>
        </div>

        <!-- Big CTA Button -->
        <a href="{{ route('employee.attendance.create') }}" 
           class="w-full py-3.5 px-4 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl shadow-lg shadow-blue-600/30 transition flex items-center justify-center space-x-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <span>
                @if(!$attendanceToday || !$attendanceToday->clock_in)
                    Buka Halaman Clock-In Sekarang
                @elseif(!$attendanceToday->clock_out)
                    Buka Halaman Clock-Out Pulang
                @else
                    Lihat Bukti Presensi Hari Ini
                @endif
            </span>
        </a>
    </div>

    <!-- 3. KPI Summary Data Cards (Attention OS Specification) -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
        <!-- Hadir -->
        <div class="bg-white p-4 rounded-xl border border-slate-200/80 shadow-sm flex flex-col justify-between">
            <div class="flex items-center justify-between text-emerald-600 mb-2">
                <span class="text-xs font-semibold text-slate-500">Hadir Bulan Ini</span>
                <div class="w-8 h-8 rounded-lg bg-emerald-50 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                </div>
            </div>
            <p class="text-2xl font-extrabold text-slate-900">{{ $monthlyPresent }} <span class="text-xs font-normal text-slate-400">Hari</span></p>
        </div>

        <!-- Terlambat -->
        <div class="bg-white p-4 rounded-xl border border-slate-200/80 shadow-sm flex flex-col justify-between">
            <div class="flex items-center justify-between text-amber-500 mb-2">
                <span class="text-xs font-semibold text-slate-500">Terlambat</span>
                <div class="w-8 h-8 rounded-lg bg-amber-50 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
            </div>
            <p class="text-2xl font-extrabold text-slate-900">{{ $monthlyLate }} <span class="text-xs font-normal text-slate-400">Kali</span></p>
        </div>

        <!-- Izin / Cuti -->
        <div class="bg-white p-4 rounded-xl border border-slate-200/80 shadow-sm flex flex-col justify-between">
            <div class="flex items-center justify-between text-blue-600 mb-2">
                <span class="text-xs font-semibold text-slate-500">Izin / Sakit</span>
                <div class="w-8 h-8 rounded-lg bg-blue-50 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                </div>
            </div>
            <p class="text-2xl font-extrabold text-slate-900">{{ $monthlyLeave }} <span class="text-xs font-normal text-slate-400">Hari</span></p>
        </div>

        <!-- Sisa Cuti Tahunan -->
        @php
            $annualBalance = $leaveBalances->firstWhere('leaveType.code', 'ANNUAL');
        @endphp
        <div class="bg-white p-4 rounded-xl border border-slate-200/80 shadow-sm flex flex-col justify-between">
            <div class="flex items-center justify-between text-indigo-600 mb-2">
                <span class="text-xs font-semibold text-slate-500">Sisa Cuti</span>
                <div class="w-8 h-8 rounded-lg bg-indigo-50 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                </div>
            </div>
            <p class="text-2xl font-extrabold text-slate-900">{{ $annualBalance->remaining ?? 12 }} <span class="text-xs font-normal text-slate-400">Hari</span></p>
        </div>
    </div>

    <!-- 4. Quick Actions Grid -->
    <div class="grid grid-cols-3 gap-3">
        <a href="{{ route('employee.leave.index') }}" class="p-4 bg-white rounded-xl border border-slate-200/80 hover:border-blue-300 hover:shadow-md transition text-center group">
            <div class="w-10 h-10 bg-blue-50 text-blue-600 rounded-lg flex items-center justify-center mx-auto mb-2 group-hover:bg-blue-600 group-hover:text-white transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
            </div>
            <span class="text-xs font-semibold text-slate-800 block">Ajukan Cuti / Izin</span>
        </a>

        <a href="{{ route('employee.overtime.index') }}" class="p-4 bg-white rounded-xl border border-slate-200/80 hover:border-blue-300 hover:shadow-md transition text-center group">
            <div class="w-10 h-10 bg-amber-50 text-amber-600 rounded-lg flex items-center justify-center mx-auto mb-2 group-hover:bg-amber-500 group-hover:text-white transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
            <span class="text-xs font-semibold text-slate-800 block">Form Lembur</span>
        </a>

        <a href="{{ route('employee.attendance.history') }}" class="p-4 bg-white rounded-xl border border-slate-200/80 hover:border-blue-300 hover:shadow-md transition text-center group">
            <div class="w-10 h-10 bg-emerald-50 text-emerald-600 rounded-lg flex items-center justify-center mx-auto mb-2 group-hover:bg-emerald-600 group-hover:text-white transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
            </div>
            <span class="text-xs font-semibold text-slate-800 block">Riwayat & Slip</span>
        </a>
    </div>

    <!-- 5. Recent 7 Days Log -->
    <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-sm">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-bold text-slate-900 text-sm">Riwayat Presensi Terbaru</h3>
            <a href="{{ route('employee.attendance.history') }}" class="text-xs font-semibold text-blue-600 hover:underline">Lihat Semua &rarr;</a>
        </div>

        @if($recentAttendances->isEmpty())
            <p class="text-xs text-slate-400 text-center py-6">Belum ada riwayat presensi tercatat.</p>
        @else
            <div class="divide-y divide-slate-100">
                @foreach($recentAttendances as $item)
                    <div class="py-3 flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <div class="w-9 h-9 rounded-lg flex items-center justify-center text-xs font-bold font-mono 
                                {{ $item->status === 'present' ? 'bg-emerald-50 text-emerald-700' : ($item->status === 'late' ? 'bg-amber-50 text-amber-700' : 'bg-blue-50 text-blue-700') }}">
                                {{ $item->date ? $item->date->format('d') : '-' }}
                            </div>
                            <div>
                                <p class="text-xs font-semibold text-slate-800">{{ $item->date ? $item->date->translatedFormat('l, d M Y') : '-' }}</p>
                                <p class="text-[11px] text-slate-400">
                                    In: <span class="font-mono text-slate-600">{{ $item->clock_in ? Carbon\Carbon::parse($item->clock_in)->format('H:i') : '--:--' }}</span> &bull;
                                    Out: <span class="font-mono text-slate-600">{{ $item->clock_out ? Carbon\Carbon::parse($item->clock_out)->format('H:i') : '--:--' }}</span>
                                </p>
                            </div>
                        </div>

                        <div>
                            @if($item->status === 'present')
                                <span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-emerald-100 text-emerald-800">Hadir</span>
                            @elseif($item->status === 'late')
                                <span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-amber-100 text-amber-800">Terlambat ({{ $item->late_minutes }}m)</span>
                            @elseif($item->status === 'leave')
                                <span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-blue-100 text-blue-800">Cuti</span>
                            @elseif($item->status === 'sick')
                                <span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-purple-100 text-purple-800">Sakit</span>
                            @else
                                <span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-slate-100 text-slate-700">{{ strtoupper($item->status) }}</span>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

</div>
@endsection
