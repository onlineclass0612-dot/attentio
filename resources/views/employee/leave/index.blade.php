@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="{ leaveModal: false, reasonModal: false, reasonTitle: '', reasonReviewer: '', reasonText: '' }">

    <!-- Header & Action -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <h1 class="text-xl font-bold text-slate-900 tracking-tight">Manajemen Cuti & Izin</h1>
            <p class="text-xs text-slate-500 mt-0.5">Pantau sisa kuota dan ajukan permohonan cuti baru</p>
        </div>
        <div class="flex items-center space-x-2">
            <a href="{{ route('employee.dashboard') }}" class="px-3.5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs rounded-xl transition flex items-center space-x-1.5 border border-slate-200">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                <span>Kembali</span>
            </a>
            <button type="button" @click="leaveModal = true" class="px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs rounded-xl shadow-md shadow-blue-600/30 transition flex items-center space-x-1.5 cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                <span>Ajukan Cuti / Izin</span>
            </button>
        </div>
    </div>

    <!-- 1. Leave Balances Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
        @foreach($balances as $bal)
            <div class="bg-white p-4 rounded-xl border border-slate-200/80 shadow-sm flex flex-col justify-between">
                <div>
                    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 block truncate">{{ $bal->leaveType->name }}</span>
                    <p class="text-2xl font-black text-slate-900 mt-1">{{ $bal->remaining }} <span class="text-xs font-normal text-slate-400">/ {{ $bal->quota }} Hari</span></p>
                </div>
                <div class="mt-3 pt-2 border-t border-slate-100 flex items-center justify-between text-[10px] text-slate-500 font-medium">
                    <span>Terpakai: {{ $bal->used }} hari</span>
                    <span class="text-blue-600 font-semibold">{{ $bal->year }}</span>
                </div>
            </div>
        @endforeach
    </div>

    <!-- 2. Leave Requests History -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="p-4 border-b border-slate-100">
            <h2 class="text-xs font-bold text-slate-800">Riwayat Pengajuan Cuti & Izin</h2>
        </div>

        @if($requests->isEmpty())
            <div class="p-8 text-center text-xs text-slate-400">
                Belum ada permohonan cuti yang diajukan.
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 text-slate-500 font-semibold border-b border-slate-200/80">
                        <tr>
                            <th class="px-4 py-3">Tipe Cuti</th>
                            <th class="px-4 py-3">Periode</th>
                            <th class="px-4 py-3">Durasi</th>
                            <th class="px-4 py-3">Alasan Pengajuan</th>
                            <th class="px-4 py-3">Lampiran</th>
                            <th class="px-4 py-3">Status & Review</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($requests as $req)
                            <tr class="hover:bg-slate-50/60 transition">
                                <td class="px-4 py-3.5 font-bold text-slate-900">
                                    {{ $req->leaveType->name }}
                                </td>
                                <td class="px-4 py-3.5 text-slate-700 whitespace-nowrap">
                                    {{ $req->start_date->format('d/m/Y') }} &ndash; {{ $req->end_date->format('d/m/Y') }}
                                </td>
                                <td class="px-4 py-3.5 font-bold text-slate-900 font-mono whitespace-nowrap">
                                    {{ $req->total_days }} Hari
                                </td>
                                <td class="px-4 py-3.5 text-slate-600 max-w-xs">
                                    {{ $req->reason }}
                                </td>
                                <td class="px-4 py-3.5 whitespace-nowrap">
                                    @if($req->attachment)
                                        <a href="{{ $req->attachment_url }}" target="_blank" class="text-blue-600 hover:underline font-semibold flex items-center">
                                            <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path></svg>
                                            Lihat File
                                        </a>
                                    @else
                                        <span class="text-slate-400">-</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3.5 min-w-[180px]">
                                    @if($req->status === 'approved')
                                        <div class="space-y-1">
                                            <span class="inline-flex items-center px-2.5 py-0.5 text-[10px] font-bold rounded-full bg-emerald-100 text-emerald-800 border border-emerald-200">
                                                <svg class="w-3 h-3 mr-1 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                                                Disetujui
                                            </span>
                                            @if($req->approver)
                                                <p class="text-[11px] text-slate-500">Oleh: <strong class="text-slate-800">{{ $req->approver->name }}</strong></p>
                                            @endif
                                        </div>
                                    @elseif($req->status === 'rejected')
                                        <div class="space-y-1.5">
                                            <div class="flex items-center space-x-2">
                                                <span class="inline-flex items-center px-2.5 py-0.5 text-[10px] font-bold rounded-full bg-rose-100 text-rose-800 border border-rose-200">
                                                    <svg class="w-3 h-3 mr-1 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
                                                    Ditolak
                                                </span>
                                                @if($req->rejection_reason)
                                                    <button type="button" 
                                                            @click="reasonTitle = 'Alasan Penolakan {{ addslashes($req->leaveType->name) }}'; reasonReviewer = '{{ addslashes($req->approver->name ?? 'Reviewer / Pimpinan') }}'; reasonText = '{{ addslashes($req->rejection_reason) }}'; reasonModal = true"
                                                            class="inline-flex items-center px-2 py-0.5 text-[10.5px] font-semibold text-rose-700 bg-rose-50 hover:bg-rose-100 border border-rose-200 rounded-md transition cursor-pointer"
                                                            title="Klik untuk melihat detail alasan penolakan">
                                                        <svg class="w-3.5 h-3.5 mr-1 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                                        <span>Lihat Alasan</span>
                                                    </button>
                                                @endif
                                            </div>
                                            @if($req->approver)
                                                <p class="text-[11px] text-slate-500">Oleh: <strong class="text-slate-800">{{ $req->approver->name }}</strong></p>
                                            @endif
                                        </div>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 text-[10px] font-bold rounded-full bg-amber-100 text-amber-800 border border-amber-200">
                                            <svg class="w-3 h-3 mr-1 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                            Menunggu Review
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <!-- Rejection Reason Modal Pop-Up -->
    <div x-show="reasonModal" 
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm"
         style="display: none;">
        <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-200 space-y-4 text-left" @click.away="reasonModal = false">
            <div class="flex items-start justify-between border-b border-slate-100 pb-3">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center font-bold border border-rose-200">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-slate-900" x-text="reasonTitle"></h3>
                        <p class="text-[11px] text-slate-500">Ditolak oleh: <strong class="text-slate-800" x-text="reasonReviewer"></strong></p>
                    </div>
                </div>
                <button type="button" @click="reasonModal = false" class="text-slate-400 hover:text-slate-600 p-1 cursor-pointer">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            <div class="bg-rose-50/70 border border-rose-200/80 rounded-xl p-4 space-y-1.5">
                <span class="block text-[10px] font-bold text-rose-800 uppercase tracking-wider">Catatan / Alasan Penolakan:</span>
                <p class="text-xs text-rose-950 font-medium leading-relaxed whitespace-pre-line" x-text="reasonText"></p>
            </div>

            <div class="flex justify-end pt-2">
                <button type="button" @click="reasonModal = false" class="px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white font-semibold text-xs rounded-xl transition cursor-pointer">
                    Tutup
                </button>
            </div>
        </div>
    </div>

    <!-- Modal Form Ajukan Cuti -->
    <div x-show="leaveModal" 
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm"
         style="display: none;">
        <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-xl border border-slate-200 relative" @click.away="leaveModal = false">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-bold text-slate-900 text-base">Formulir Pengajuan Cuti / Izin</h3>
                <button type="button" @click="leaveModal = false" class="text-slate-400 hover:text-slate-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            <form action="{{ route('employee.leave.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Tipe Cuti / Izin</label>
                    <select name="leave_type_id" required
                            class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-xs text-slate-900 focus:bg-white focus:ring-2 focus:ring-blue-600">
                        @foreach($leaveTypes as $lt)
                            <option value="{{ $lt->id }}">{{ $lt->name }} (Kuota: {{ $lt->default_quota }} Hari)</option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Tanggal Mulai</label>
                        <input type="date" name="start_date" required min="{{ date('Y-m-d') }}"
                               class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-xs text-slate-900 focus:bg-white focus:ring-2 focus:ring-blue-600">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Tanggal Selesai</label>
                        <input type="date" name="end_date" required min="{{ date('Y-m-d') }}"
                               class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-xs text-slate-900 focus:bg-white focus:ring-2 focus:ring-blue-600">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Alasan Pengajuan</label>
                    <textarea name="reason" rows="3" required placeholder="Jelaskan keperluan cuti atau izin Anda..."
                              class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-xs text-slate-900 focus:bg-white focus:ring-2 focus:ring-blue-600"></textarea>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Lampiran / Surat Dokter (Opsional/Wajib untuk Sakit)</label>
                    <input type="file" name="attachment" accept=".jpeg,.jpg,.png,.pdf"
                           class="w-full px-3 py-1.5 bg-slate-50 border border-slate-200 rounded-lg text-xs text-slate-500 file:mr-2 file:py-1 file:px-2 file:rounded file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                </div>

                <div class="flex justify-end space-x-2 pt-2">
                    <button type="button" @click="leaveModal = false" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-lg transition">
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
