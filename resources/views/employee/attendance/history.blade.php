@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="{ correctionModal: false, selectedDate: '' }">

    <!-- Header & Filter -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm">
        <div>
            <h1 class="text-lg font-bold text-slate-900 tracking-tight">Riwayat Presensi</h1>
            <p class="text-xs text-slate-500 mt-0.5">Daftar kehadiran harian dan pengajuan koreksi</p>
        </div>

        <div class="flex items-center space-x-2 self-start sm:self-auto">
            <form method="GET" action="{{ route('employee.attendance.history') }}" class="flex items-center space-x-2">
                <input type="month" name="month" value="{{ $month }}" 
                       class="px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-600">
                <button type="submit" class="px-3.5 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs rounded-lg transition">
                    Filter
                </button>
            </form>
            <a href="{{ route('employee.dashboard') }}" class="px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs rounded-lg transition flex items-center space-x-1.5 border border-slate-200">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                <span>Kembali</span>
            </a>
        </div>
    </div>

    <!-- Attendance Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="p-4 border-b border-slate-100 flex items-center justify-between">
            <span class="text-xs font-bold text-slate-800">Bulan: {{ Carbon\Carbon::parse($month.'-01')->translatedFormat('F Y') }}</span>
            <button type="button" @click="correctionModal = true" class="text-xs font-semibold text-blue-600 hover:underline flex items-center">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                Ajukan Koreksi Presensi
            </button>
        </div>

        @if($attendances->isEmpty())
            <div class="p-8 text-center text-xs text-slate-400">
                Tidak ada data presensi yang ditemukan untuk bulan ini.
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 text-slate-500 font-semibold border-b border-slate-200/80">
                        <tr>
                            <th class="px-4 py-3">Tanggal</th>
                            <th class="px-4 py-3">Masuk (In)</th>
                            <th class="px-4 py-3">Pulang (Out)</th>
                            <th class="px-4 py-3">Durasi</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3">Catatan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($attendances as $att)
                            <tr class="hover:bg-slate-50/60 transition">
                                <td class="px-4 py-3.5 font-medium text-slate-900">
                                    {{ $att->date ? $att->date->translatedFormat('d M Y') : '-' }}
                                    <span class="block text-[10px] text-slate-400 font-normal">{{ $att->date ? $att->date->translatedFormat('l') : '' }}</span>
                                </td>
                                <td class="px-4 py-3.5">
                                    <span class="font-mono font-semibold text-slate-800">
                                        {{ $att->clock_in ? Carbon\Carbon::parse($att->clock_in)->format('H:i') : '--:--' }}
                                    </span>
                                    @if($att->late_minutes > 0)
                                        <span class="block text-[10px] text-amber-600 font-medium">+{{ $att->late_minutes }}m telat</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3.5">
                                    <span class="font-mono font-semibold text-slate-800">
                                        {{ $att->clock_out ? Carbon\Carbon::parse($att->clock_out)->format('H:i') : '--:--' }}
                                    </span>
                                    @if($att->early_leave_minutes > 0)
                                        <span class="block text-[10px] text-rose-600 font-medium">-{{ $att->early_leave_minutes }}m awal</span>
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
                                <td class="px-4 py-3.5 text-slate-500 max-w-xs truncate">
                                    {{ $att->notes ?? '-' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <!-- Modal Ajukan Koreksi Presensi -->
    <div x-show="correctionModal" 
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm"
         style="display: none;">
        <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-xl border border-slate-200 relative" @click.away="correctionModal = false">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-bold text-slate-900 text-base">Ajukan Koreksi Presensi</h3>
                <button type="button" @click="correctionModal = false" class="text-slate-400 hover:text-slate-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            <form action="{{ route('employee.attendance.correction') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Tanggal Absensi</label>
                    <input type="date" name="date" required max="{{ date('Y-m-d') }}"
                           class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-xs text-slate-900 focus:bg-white focus:ring-2 focus:ring-blue-600">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Usulan Jam Masuk</label>
                        <input type="time" name="proposed_clock_in"
                               class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-xs text-slate-900 focus:bg-white focus:ring-2 focus:ring-blue-600">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Usulan Jam Pulang</label>
                        <input type="time" name="proposed_clock_out"
                               class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-xs text-slate-900 focus:bg-white focus:ring-2 focus:ring-blue-600">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Alasan Koreksi</label>
                    <textarea name="reason" rows="3" required placeholder="Jelaskan alasan kendala absensi (contoh: HP lowbat, dinas luar)..."
                              class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-xs text-slate-900 focus:bg-white focus:ring-2 focus:ring-blue-600"></textarea>
                </div>

                <div class="flex justify-end space-x-2 pt-2">
                    <button type="button" @click="correctionModal = false" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-lg transition">
                        Batal
                    </button>
                    <button type="submit" class="px-4 py-2 text-xs font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-lg transition shadow-md shadow-blue-600/30">
                        Kirim Pengajuan
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
