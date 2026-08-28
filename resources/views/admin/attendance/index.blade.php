@extends('layouts.admin', ['title' => 'Log Presensi', 'header' => 'Log & Rekap Presensi'])

@section('content')
<div class="space-y-6" x-data="{ photoModal: false, photoUrl: '', photoTitle: '' }">

    <!-- Header & Action -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-slate-900 tracking-tight">Log Presensi Harian</h1>
            <p class="text-xs text-slate-500 mt-0.5">Monitoring kehadiran karyawan dan verifikasi foto selfie</p>
        </div>
        <div class="flex items-center space-x-2">
            <a href="{{ route('admin.reports.index', ['start_date' => $date, 'end_date' => $date]) }}" class="px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs rounded-xl shadow-sm transition flex items-center space-x-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                <span>Export Laporan</span>
            </a>
            @hasanyrole('Super Admin|HR Manager')
            <a href="{{ route('admin.attendance.create') }}" class="px-3.5 py-2 bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs rounded-xl shadow-md shadow-blue-600/30 transition flex items-center space-x-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                <span>Input Manual</span>
            </a>
            @endhasanyrole
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm">
        <form method="GET" action="{{ route('admin.attendance.index') }}" class="grid grid-cols-1 sm:grid-cols-4 gap-3">
            <div>
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Tanggal</label>
                <input type="date" name="date" value="{{ $date }}"
                       class="w-full px-3 py-1.5 bg-slate-50 border border-slate-200 rounded-lg text-xs text-slate-900 focus:bg-white focus:ring-2 focus:ring-blue-600">
            </div>
            <div>
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Departemen</label>
                <select name="department_id" class="w-full px-3 py-1.5 bg-slate-50 border border-slate-200 rounded-lg text-xs text-slate-900 focus:bg-white focus:ring-2 focus:ring-blue-600">
                    <option value="">Semua Departemen</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Status Kehadiran</label>
                <select name="status" class="w-full px-3 py-1.5 bg-slate-50 border border-slate-200 rounded-lg text-xs text-slate-900 focus:bg-white focus:ring-2 focus:ring-blue-600">
                    <option value="">Semua Status</option>
                    <option value="present" {{ request('status') == 'present' ? 'selected' : '' }}>Hadir Tepat Waktu</option>
                    <option value="late" {{ request('status') == 'late' ? 'selected' : '' }}>Terlambat</option>
                    <option value="leave" {{ request('status') == 'leave' ? 'selected' : '' }}>Cuti</option>
                    <option value="sick" {{ request('status') == 'sick' ? 'selected' : '' }}>Sakit</option>
                    <option value="absent" {{ request('status') == 'absent' ? 'selected' : '' }}>Alpha / Absent</option>
                </select>
            </div>
            <div class="flex items-end">
                <button type="submit" class="w-full py-2 px-4 bg-slate-900 hover:bg-slate-800 text-white font-semibold text-xs rounded-lg transition">
                    Filter Data
                </button>
            </div>
        </form>
    </div>

    <!-- Attendance Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-500 font-semibold border-b border-slate-200/80">
                    <tr>
                        <th class="px-4 py-3.5">Karyawan</th>
                        <th class="px-4 py-3.5">Departemen</th>
                        <th class="px-4 py-3.5">Jam Masuk (In)</th>
                        <th class="px-4 py-3.5">Jam Pulang (Out)</th>
                        <th class="px-4 py-3.5">Lokasi / Jarak</th>
                        <th class="px-4 py-3.5">Status</th>
                        <th class="px-4 py-3.5">Foto Selfie</th>
                        <th class="px-4 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($attendances as $att)
                        <tr class="hover:bg-slate-50/60 transition">
                            <td class="px-4 py-3.5">
                                <div class="flex items-center space-x-2.5">
                                    <img src="{{ $att->employee?->avatar_url }}" class="w-7 h-7 rounded-full object-cover">
                                    <div>
                                        <p class="font-bold text-slate-900">{{ $att->employee?->user?->name ?? 'User' }}</p>
                                        <p class="text-[10px] text-slate-400 font-mono">{{ $att->employee?->nik ?? '-' }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3.5 text-slate-600">
                                {{ $att->employee?->department?->name ?? '-' }}
                            </td>
                            <td class="px-4 py-3.5">
                                <span class="font-mono font-semibold text-slate-900">
                                    {{ $att->clock_in ? Carbon\Carbon::parse($att->clock_in)->format('H:i') : '--:--' }}
                                </span>
                                @if($att->late_minutes > 0)
                                    <span class="block text-[10px] text-amber-600 font-medium">+{{ $att->late_minutes }}m telat</span>
                                @endif
                            </td>
                            <td class="px-4 py-3.5">
                                <span class="font-mono font-semibold text-slate-900">
                                    {{ $att->clock_out ? Carbon\Carbon::parse($att->clock_out)->format('H:i') : '--:--' }}
                                </span>
                            </td>
                            <td class="px-4 py-3.5">
                                <p class="text-slate-800 font-semibold">{{ $att->branch?->name ?? 'Kantor Pusat' }}</p>
                                @if($att->in_distance_meters !== null)
                                    <span class="text-[10px] text-slate-500 font-mono">Jarak: {{ $att->in_distance_meters }}m</span>
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
                            <td class="px-4 py-3.5">
                                @if($att->in_photo)
                                    <button type="button" @click="photoUrl = '{{ $att->in_photo_url }}'; photoTitle = 'Foto Clock-In: {{ $att->employee?->user?->name }}'; photoModal = true" 
                                            class="w-7 h-7 rounded-lg overflow-hidden border border-slate-200 hover:ring-2 hover:ring-blue-600 transition inline-block">
                                        <img src="{{ $att->in_photo_url }}" class="w-full h-full object-cover">
                                    </button>
                                @else
                                    <span class="text-slate-400">-</span>
                                @endif
                            </td>
                            <td class="px-4 py-3.5 text-right space-x-1">
                                @hasanyrole('Super Admin|HR Manager')
                                <a href="{{ route('admin.attendance.edit', $att->id) }}" class="px-3 py-1.5 text-[11px] font-semibold text-blue-600 hover:bg-blue-50 border border-blue-200 rounded-lg transition inline-flex items-center space-x-1">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                    <span>Edit</span>
                                </a>
                                @else
                                <span class="text-[11px] text-slate-400 font-medium">Hanya Lihat</span>
                                @endhasanyrole
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="p-8 text-center text-xs text-slate-400">
                                Tidak ada log presensi untuk kriteria filter ini.
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

    <!-- Photo Modal -->
    <div x-show="photoModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm" style="display: none;">
        <div class="bg-white rounded-2xl max-w-sm w-full p-5 shadow-2xl border border-slate-200 text-center" @click.away="photoModal = false">
            <h3 class="text-xs font-bold text-slate-900 mb-3" x-text="photoTitle"></h3>
            <img :src="photoUrl" class="w-full rounded-xl object-cover aspect-video mb-4 border border-slate-200">
            <button type="button" @click="photoModal = false" class="w-full py-2 bg-slate-900 text-white font-semibold text-xs rounded-lg">
                Tutup
            </button>
        </div>
    </div>

</div>
@endsection
