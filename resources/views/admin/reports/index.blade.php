@extends('layouts.admin', ['title' => 'Laporan Presensi', 'header' => 'Laporan & Export Presensi'])

@section('content')
<div class="space-y-6">

    <!-- Header & Action Buttons -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-slate-900 tracking-tight">Laporan Kehadiran Karyawan</h1>
            <p class="text-xs text-slate-500 mt-0.5">Rekapitulasi data absensi siap pakai untuk integrasi penggajian (Payroll)</p>
        </div>
        @hasanyrole('Super Admin|HR Manager')
        <div class="flex items-center space-x-2">
            <a href="{{ route('admin.reports.excel', request()->all()) }}" class="px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow-md shadow-emerald-600/30 transition flex items-center space-x-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                <span>Export Excel (.xlsx)</span>
            </a>
            <a href="{{ route('admin.reports.pdf', request()->all()) }}" class="px-4 py-2.5 bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs rounded-xl shadow-md shadow-rose-600/30 transition flex items-center space-x-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                <span>Download PDF</span>
            </a>
        </div>
        @endhasanyrole
    </div>

    @if(isset($supervisorDepartment) && $supervisorDepartment)
        <x-radix-alert type="info" title="Rekapitulasi Khusus Divisi: {{ $supervisorDepartment->name }}">
            Menampilkan data laporan kehadiran karyawan di divisi <strong>{{ $supervisorDepartment->name }} ({{ $supervisorDepartment->code ?? '-' }})</strong>.
        </x-radix-alert>
    @endif

    <!-- Filter Form -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm">
        <form method="GET" action="{{ route('admin.reports.index') }}" class="grid grid-cols-1 sm:grid-cols-5 gap-3">
            <div>
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Tanggal Mulai</label>
                <input type="date" name="start_date" value="{{ $startDate ?? '' }}"
                       class="w-full px-3 py-1.5 bg-slate-50 border border-slate-200 rounded-lg text-xs text-slate-900 focus:bg-white focus:ring-2 focus:ring-blue-600">
            </div>
            <div>
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Tanggal Selesai</label>
                <input type="date" name="end_date" value="{{ $endDate ?? '' }}"
                       class="w-full px-3 py-1.5 bg-slate-50 border border-slate-200 rounded-lg text-xs text-slate-900 focus:bg-white focus:ring-2 focus:ring-blue-600">
            </div>
            <div>
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Departemen</label>
                <select name="department_id" class="w-full px-3 py-1.5 bg-slate-50 border border-slate-200 rounded-lg text-xs text-slate-900 focus:bg-white focus:ring-2 focus:ring-blue-600">
                    @if(!isset($supervisorDepartment) || !$supervisorDepartment)
                        <option value="">Semua Departemen</option>
                    @endif
                    @foreach($departments as $dept)
                        <option value="{{ $dept->id }}" {{ ($departmentId ?? '') == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Kantor / Cabang</label>
                <select name="branch_id" class="w-full px-3 py-1.5 bg-slate-50 border border-slate-200 rounded-lg text-xs text-slate-900 focus:bg-white focus:ring-2 focus:ring-blue-600">
                    <option value="">Semua Kantor</option>
                    @foreach($branches as $br)
                        <option value="{{ $br->id }}" {{ ($branchId ?? '') == $br->id ? 'selected' : '' }}>{{ $br->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end">
                <button type="submit" class="w-full py-2 px-4 bg-slate-900 hover:bg-slate-800 text-white font-semibold text-xs rounded-lg transition">
                    Tampilkan Rekap
                </button>
            </div>
        </form>
    </div>

    <!-- Summary Metrics -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
        <div class="bg-white p-4 rounded-xl border border-slate-200/80 shadow-sm">
            <span class="text-xs font-semibold text-slate-500 block">Total Hadir Tepat Waktu</span>
            <p class="text-2xl font-black text-emerald-600 mt-1">{{ $totalPresent }} <span class="text-xs font-normal text-slate-400">Record</span></p>
        </div>
        <div class="bg-white p-4 rounded-xl border border-slate-200/80 shadow-sm">
            <span class="text-xs font-semibold text-slate-500 block">Total Terlambat</span>
            <p class="text-2xl font-black text-amber-500 mt-1">{{ $totalLate }} <span class="text-xs font-normal text-slate-400">Kali</span></p>
        </div>
        <div class="bg-white p-4 rounded-xl border border-slate-200/80 shadow-sm">
            <span class="text-xs font-semibold text-slate-500 block">Akumulasi Menit Telat</span>
            <p class="text-2xl font-black text-rose-600 mt-1">{{ $totalLateMinutes }} <span class="text-xs font-normal text-slate-400">Menit</span></p>
        </div>
        <div class="bg-white p-4 rounded-xl border border-slate-200/80 shadow-sm">
            <span class="text-xs font-semibold text-slate-500 block">Total Cuti & Izin</span>
            <p class="text-2xl font-black text-blue-600 mt-1">{{ $totalLeave }} <span class="text-xs font-normal text-slate-400">Hari</span></p>
        </div>
    </div>

    <!-- Reports Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-500 font-semibold border-b border-slate-200/80">
                    <tr>
                        <th class="px-4 py-3.5">Tanggal</th>
                        <th class="px-4 py-3.5">NIK & Karyawan</th>
                        <th class="px-4 py-3.5">Departemen</th>
                        <th class="px-4 py-3.5">Jam Masuk</th>
                        <th class="px-4 py-3.5">Jam Pulang</th>
                        <th class="px-4 py-3.5">Terlambat</th>
                        <th class="px-4 py-3.5">Durasi Kerja</th>
                        <th class="px-4 py-3.5">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($attendances as $att)
                        <tr class="hover:bg-slate-50/60 transition">
                            <td class="px-4 py-3.5 font-medium text-slate-900 font-mono">
                                {{ $att->date ? $att->date->format('d/m/Y') : '-' }}
                            </td>
                            <td class="px-4 py-3.5">
                                <p class="font-bold text-slate-900">{{ $att->employee?->user?->name ?? 'User' }}</p>
                                <p class="text-[10px] text-slate-400 font-mono">{{ $att->employee?->nik ?? '-' }}</p>
                            </td>
                            <td class="px-4 py-3.5 text-slate-600">
                                {{ $att->employee?->department?->name ?? '-' }}
                            </td>
                            <td class="px-4 py-3.5 font-mono text-slate-800">
                                {{ $att->clock_in ?? '--:--' }}
                            </td>
                            <td class="px-4 py-3.5 font-mono text-slate-800">
                                {{ $att->clock_out ?? '--:--' }}
                            </td>
                            <td class="px-4 py-3.5 font-mono">
                                @if($att->late_minutes > 0)
                                    <span class="text-amber-600 font-bold">+{{ $att->late_minutes }}m</span>
                                @else
                                    <span class="text-slate-400">0m</span>
                                @endif
                            </td>
                            <td class="px-4 py-3.5 font-mono text-slate-600">
                                @if($att->work_duration_minutes > 0)
                                    {{ floor($att->work_duration_minutes / 60) }}j {{ $att->work_duration_minutes % 60 }}m
                                @else
                                    -
                                @endif
                            </td>
                            <td class="px-4 py-3.5">
                                @if($att->status === 'present')
                                    <span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-emerald-100 text-emerald-800">Hadir</span>
                                @elseif($att->status === 'late')
                                    <span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-amber-100 text-amber-800">Terlambat</span>
                                @elseif($att->status === 'leave')
                                    <span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-blue-100 text-blue-800">Cuti</span>
                                @elseif($att->status === 'sick')
                                    <span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-purple-100 text-purple-800">Sakit</span>
                                @else
                                    <span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-slate-100 text-slate-700">{{ strtoupper($att->status) }}</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="p-8 text-center text-xs text-slate-400">
                                Tidak ada data presensi pada rentang tanggal ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($attendances->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $attendances->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
