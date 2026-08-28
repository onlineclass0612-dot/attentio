@extends('layouts.admin', ['title' => 'Pusat Persetujuan', 'header' => 'Pusat Persetujuan (Approval Center)'])

@section('content')
<div class="space-y-6" x-data="{ tab: 'leaves', rejectModal: false, rejectUrl: '', rejectTitle: '' }">

    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-slate-900 tracking-tight">Pusat Persetujuan</h1>
            <p class="text-xs text-slate-500 mt-0.5">Tinjau dan proses permohonan cuti, lembur, dan koreksi kehadiran</p>
        </div>
    </div>

    @if(isset($supervisorDepartment) && $supervisorDepartment)
        <x-radix-alert type="info" title="Pusat Persetujuan Divisi: {{ $supervisorDepartment->name }}">
            Menampilkan antrean permohonan cuti, lembur, dan koreksi presensi dari karyawan divisi <strong>{{ $supervisorDepartment->name }}</strong>.
        </x-radix-alert>
    @endif

    <!-- Navigation Tabs -->
    <div class="flex space-x-2 border-b border-slate-200">
        <button type="button" @click="tab = 'leaves'" 
                :class="tab === 'leaves' ? 'border-blue-600 text-blue-600 font-bold' : 'border-transparent text-slate-500 hover:text-slate-700'"
                class="px-4 py-2.5 border-b-2 text-xs font-semibold transition flex items-center space-x-2">
            <span>Cuti & Izin</span>
            @if($leaves->count() > 0)
                <span class="px-1.5 py-0.5 text-[10px] font-bold bg-blue-100 text-blue-800 rounded-full">{{ $leaves->count() }}</span>
            @endif
        </button>

        <button type="button" @click="tab = 'overtimes'" 
                :class="tab === 'overtimes' ? 'border-amber-500 text-amber-600 font-bold' : 'border-transparent text-slate-500 hover:text-slate-700'"
                class="px-4 py-2.5 border-b-2 text-xs font-semibold transition flex items-center space-x-2">
            <span>Lembur</span>
            @if($overtimes->count() > 0)
                <span class="px-1.5 py-0.5 text-[10px] font-bold bg-amber-100 text-amber-800 rounded-full">{{ $overtimes->count() }}</span>
            @endif
        </button>

        <button type="button" @click="tab = 'corrections'" 
                :class="tab === 'corrections' ? 'border-purple-600 text-purple-600 font-bold' : 'border-transparent text-slate-500 hover:text-slate-700'"
                class="px-4 py-2.5 border-b-2 text-xs font-semibold transition flex items-center space-x-2">
            <span>Koreksi Presensi</span>
            @if($corrections->count() > 0)
                <span class="px-1.5 py-0.5 text-[10px] font-bold bg-purple-100 text-purple-800 rounded-full">{{ $corrections->count() }}</span>
            @endif
        </button>
    </div>

    <!-- 1. Cuti & Izin Requests -->
    <div x-show="tab === 'leaves'" class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-500 font-semibold border-b border-slate-200/80">
                    <tr>
                        <th class="px-4 py-3.5">Karyawan</th>
                        <th class="px-4 py-3.5">Tipe Cuti</th>
                        <th class="px-4 py-3.5">Rentang Tanggal</th>
                        <th class="px-4 py-3.5">Durasi</th>
                        <th class="px-4 py-3.5">Alasan</th>
                        <th class="px-4 py-3.5">Lampiran</th>
                        <th class="px-4 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($leaves as $item)
                        <tr class="hover:bg-slate-50/60 transition">
                            <td class="px-4 py-3.5">
                                <div class="flex items-center space-x-2.5">
                                    <img src="{{ $item->employee?->avatar_url }}" class="w-7 h-7 rounded-full object-cover">
                                    <div>
                                        <p class="font-bold text-slate-900">{{ $item->employee?->user?->name }}</p>
                                        <p class="text-[10px] text-slate-400 font-mono">{{ $item->employee?->nik }} &bull; {{ $item->employee?->department?->name }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3.5 font-semibold text-slate-800">
                                {{ $item->leaveType->name }}
                            </td>
                            <td class="px-4 py-3.5 text-slate-700">
                                {{ $item->start_date->format('d/m/Y') }} &ndash; {{ $item->end_date->format('d/m/Y') }}
                            </td>
                            <td class="px-4 py-3.5 font-bold font-mono text-slate-900">
                                {{ $item->total_days }} Hari
                            </td>
                            <td class="px-4 py-3.5 text-slate-600 max-w-xs truncate">
                                {{ $item->reason }}
                            </td>
                            <td class="px-4 py-3.5">
                                @if($item->attachment)
                                    <a href="{{ $item->attachment_url }}" target="_blank" class="text-blue-600 hover:underline font-semibold flex items-center">
                                        <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path></svg>
                                        Lihat File
                                    </a>
                                @else
                                    <span class="text-slate-400">-</span>
                                @endif
                            </td>
                            <td class="px-4 py-3.5 text-right space-x-1.5 whitespace-nowrap">
                                <form action="{{ route('admin.approvals.leave.approve', $item->id) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="px-3 py-1.5 text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg transition shadow-sm">
                                        Setujui
                                    </button>
                                </form>
                                <button type="button" @click="rejectUrl = '{{ route('admin.approvals.leave.reject', $item->id) }}'; rejectTitle = 'Tolak Pengajuan Cuti: {{ $item->employee?->user?->name }}'; rejectModal = true"
                                        class="px-3 py-1.5 text-xs font-semibold text-rose-600 hover:bg-rose-50 border border-rose-200 rounded-lg transition">
                                    Tolak
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="p-8 text-center text-xs text-slate-400">
                                Tidak ada pengajuan cuti atau izin yang menunggu persetujuan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- 2. Overtime Requests -->
    <div x-show="tab === 'overtimes'" class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden" style="display: none;">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-500 font-semibold border-b border-slate-200/80">
                    <tr>
                        <th class="px-4 py-3.5">Karyawan</th>
                        <th class="px-4 py-3.5">Tanggal</th>
                        <th class="px-4 py-3.5">Jam Mulai & Selesai</th>
                        <th class="px-4 py-3.5">Durasi</th>
                        <th class="px-4 py-3.5">Uraian Tugas</th>
                        <th class="px-4 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($overtimes as $ovt)
                        <tr class="hover:bg-slate-50/60 transition">
                            <td class="px-4 py-3.5">
                                <div class="flex items-center space-x-2.5">
                                    <img src="{{ $ovt->employee?->avatar_url }}" class="w-7 h-7 rounded-full object-cover">
                                    <div>
                                        <p class="font-bold text-slate-900">{{ $ovt->employee?->user?->name }}</p>
                                        <p class="text-[10px] text-slate-400 font-mono">{{ $ovt->employee?->nik }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3.5 text-slate-700">
                                {{ $ovt->date ? $ovt->date->format('d/m/Y') : '-' }}
                            </td>
                            <td class="px-4 py-3.5 font-mono text-slate-800">
                                {{ $ovt->start_time }} &ndash; {{ $ovt->end_time }} WIB
                            </td>
                            <td class="px-4 py-3.5 font-bold font-mono text-amber-600">
                                {{ $ovt->duration_hours }} Jam
                            </td>
                            <td class="px-4 py-3.5 text-slate-600 max-w-xs truncate">
                                {{ $ovt->task_description }}
                            </td>
                            <td class="px-4 py-3.5 text-right space-x-1.5 whitespace-nowrap">
                                <form action="{{ route('admin.approvals.overtime.approve', $ovt->id) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="px-3 py-1.5 text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg transition shadow-sm">
                                        Setujui
                                    </button>
                                </form>
                                <button type="button" @click="rejectUrl = '{{ route('admin.approvals.overtime.reject', $ovt->id) }}'; rejectTitle = 'Tolak Lembur: {{ $ovt->employee?->user?->name }}'; rejectModal = true"
                                        class="px-3 py-1.5 text-xs font-semibold text-rose-600 hover:bg-rose-50 border border-rose-200 rounded-lg transition">
                                    Tolak
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-8 text-center text-xs text-slate-400">
                                Tidak ada pengajuan lembur yang menunggu persetujuan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- 3. Attendance Corrections -->
    <div x-show="tab === 'corrections'" class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden" style="display: none;">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-500 font-semibold border-b border-slate-200/80">
                    <tr>
                        <th class="px-4 py-3.5">Karyawan</th>
                        <th class="px-4 py-3.5">Tanggal</th>
                        <th class="px-4 py-3.5">Usulan Jam</th>
                        <th class="px-4 py-3.5">Alasan Kendala</th>
                        <th class="px-4 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($corrections as $cor)
                        <tr class="hover:bg-slate-50/60 transition">
                            <td class="px-4 py-3.5">
                                <div class="flex items-center space-x-2.5">
                                    <img src="{{ $cor->employee?->avatar_url }}" class="w-7 h-7 rounded-full object-cover">
                                    <div>
                                        <p class="font-bold text-slate-900">{{ $cor->employee?->user?->name }}</p>
                                        <p class="text-[10px] text-slate-400 font-mono">{{ $cor->employee?->nik }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3.5 text-slate-700">
                                {{ $cor->date ? $cor->date->format('d/m/Y') : '-' }}
                            </td>
                            <td class="px-4 py-3.5 font-mono text-slate-800">
                                In: {{ $cor->proposed_clock_in ?? '-' }} &bull; Out: {{ $cor->proposed_clock_out ?? '-' }}
                            </td>
                            <td class="px-4 py-3.5 text-slate-600 max-w-xs truncate">
                                {{ $cor->reason }}
                            </td>
                            <td class="px-4 py-3.5 text-right space-x-1.5 whitespace-nowrap">
                                <form action="{{ route('admin.approvals.correction.approve', $cor->id) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="px-3 py-1.5 text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg transition shadow-sm">
                                        Setujui
                                    </button>
                                </form>
                                <button type="button" @click="rejectUrl = '{{ route('admin.approvals.correction.reject', $cor->id) }}'; rejectTitle = 'Tolak Koreksi: {{ $cor->employee?->user?->name }}'; rejectModal = true"
                                        class="px-3 py-1.5 text-xs font-semibold text-rose-600 hover:bg-rose-50 border border-rose-200 rounded-lg transition">
                                    Tolak
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="p-8 text-center text-xs text-slate-400">
                                Tidak ada pengajuan koreksi presensi yang pending.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Reject Modal -->
    <div x-show="rejectModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm" style="display: none;">
        <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-xl border border-slate-200" @click.away="rejectModal = false">
            <h3 class="font-bold text-slate-900 text-sm mb-3" x-text="rejectTitle"></h3>
            <form :action="rejectUrl" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Alasan Penolakan</label>
                    <textarea name="rejection_reason" rows="3" required placeholder="Tuliskan alasan penolakan untuk karyawan..."
                              class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-xs text-slate-900 focus:bg-white focus:ring-2 focus:ring-blue-600"></textarea>
                </div>
                <div class="flex justify-end space-x-2">
                    <button type="button" @click="rejectModal = false" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-lg">
                        Batal
                    </button>
                    <button type="submit" class="px-4 py-2 text-xs font-bold text-white bg-rose-600 hover:bg-rose-700 rounded-lg shadow-sm">
                        Konfirmasi Tolak
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
