@extends('layouts.admin', ['title' => 'Tambah Presensi Manual', 'header' => 'Input Presensi Manual'])

@section('content')
<div class="max-w-xl mx-auto space-y-6">

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-slate-900 tracking-tight">Input Presensi Manual</h1>
            <p class="text-xs text-slate-500 mt-0.5">Penyesuaian catatan kehadiran karyawan oleh HR</p>
        </div>
        <a href="{{ route('admin.attendance.index') }}" class="text-xs font-semibold text-slate-600 hover:text-slate-900">
            &larr; Kembali
        </a>
    </div>

    <form action="{{ route('admin.attendance.store') }}" method="POST" class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-sm space-y-4">
        @csrf

        <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1">Pilih Karyawan</label>
            <select name="employee_id" required class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-lg text-xs text-slate-900 focus:bg-white focus:ring-2 focus:ring-blue-600">
                @foreach($employees as $emp)
                    <option value="{{ $emp->id }}">{{ $emp->nik }} - {{ $emp->user->name }} ({{ $emp->department->name ?? 'Dept' }})</option>
                @endforeach
            </select>
        </div>

        <div class="grid grid-cols-2 gap-3">
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Tanggal</label>
                <input type="date" name="date" value="{{ date('Y-m-d') }}" required
                       class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-lg text-xs text-slate-900 focus:bg-white focus:ring-2 focus:ring-blue-600">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Status Kehadiran</label>
                <select name="status" required class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-lg text-xs text-slate-900 focus:bg-white focus:ring-2 focus:ring-blue-600">
                    <option value="present">Hadir Tepat Waktu</option>
                    <option value="late">Terlambat</option>
                    <option value="early_leave">Pulang Awal</option>
                    <option value="leave">Cuti</option>
                    <option value="sick">Sakit</option>
                    <option value="permission">Izin / Dinas Luar</option>
                    <option value="absent">Alpha / Tidak Hadir</option>
                </select>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-3">
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Jam Masuk (Clock In)</label>
                <input type="time" name="clock_in" value="08:00"
                       class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-lg text-xs text-slate-900 focus:bg-white focus:ring-2 focus:ring-blue-600">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Jam Pulang (Clock Out)</label>
                <input type="time" name="clock_out" value="17:00"
                       class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-lg text-xs text-slate-900 focus:bg-white focus:ring-2 focus:ring-blue-600">
            </div>
        </div>

        <div class="grid grid-cols-2 gap-3">
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Lokasi Kantor</label>
                <select name="branch_id" class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-lg text-xs text-slate-900 focus:bg-white focus:ring-2 focus:ring-blue-600">
                    @foreach($branches as $br)
                        <option value="{{ $br->id }}">{{ $br->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Shift</label>
                <select name="shift_id" class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-lg text-xs text-slate-900 focus:bg-white focus:ring-2 focus:ring-blue-600">
                    @foreach($shifts as $sh)
                        <option value="{{ $sh->id }}">{{ $sh->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1">Catatan Tambahan (Opsional)</label>
            <textarea name="notes" rows="2" placeholder="Contoh: Input penyesuaian manual oleh HR..."
                      class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-lg text-xs text-slate-900 focus:bg-white focus:ring-2 focus:ring-blue-600"></textarea>
        </div>

        <div class="pt-3 border-t border-slate-100 flex items-center justify-end space-x-2">
            <a href="{{ route('admin.attendance.index') }}" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-lg transition">
                Batal
            </a>
            <button type="submit" class="px-4 py-2 text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 rounded-lg transition shadow-md shadow-blue-600/30">
                Simpan Presensi
            </button>
        </div>
    </form>

</div>
@endsection
