@extends('layouts.admin', ['title' => 'Edit Presensi', 'header' => 'Edit Catatan Presensi'])

@section('content')
<div class="max-w-xl mx-auto space-y-6">

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-slate-900 tracking-tight">Edit Presensi: {{ $attendance->employee->user->name }}</h1>
            <p class="text-xs text-slate-500 mt-0.5">Tanggal: {{ $attendance->date->format('d/m/Y') }} (NIK: {{ $attendance->employee->nik }})</p>
        </div>
        <a href="{{ route('admin.attendance.index', ['date' => $attendance->date->format('Y-m-d')]) }}" class="text-xs font-semibold text-slate-600 hover:text-slate-900">
            &larr; Kembali
        </a>
    </div>

    <form action="{{ route('admin.attendance.update', $attendance->id) }}" method="POST" class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-sm space-y-4">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-2 gap-3">
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Status Kehadiran</label>
                <select name="status" required class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-lg text-xs text-slate-900 focus:bg-white focus:ring-2 focus:ring-blue-600">
                    <option value="present" {{ old('status', $attendance->status) == 'present' ? 'selected' : '' }}>Hadir Tepat Waktu</option>
                    <option value="late" {{ old('status', $attendance->status) == 'late' ? 'selected' : '' }}>Terlambat</option>
                    <option value="early_leave" {{ old('status', $attendance->status) == 'early_leave' ? 'selected' : '' }}>Pulang Awal</option>
                    <option value="leave" {{ old('status', $attendance->status) == 'leave' ? 'selected' : '' }}>Cuti</option>
                    <option value="sick" {{ old('status', $attendance->status) == 'sick' ? 'selected' : '' }}>Sakit</option>
                    <option value="permission" {{ old('status', $attendance->status) == 'permission' ? 'selected' : '' }}>Izin / Tugas Luar</option>
                    <option value="absent" {{ old('status', $attendance->status) == 'absent' ? 'selected' : '' }}>Alpha / Tidak Hadir</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Lokasi Kantor</label>
                <select name="branch_id" class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-lg text-xs text-slate-900 focus:bg-white focus:ring-2 focus:ring-blue-600">
                    @foreach($branches as $br)
                        <option value="{{ $br->id }}" {{ old('branch_id', $attendance->branch_id) == $br->id ? 'selected' : '' }}>{{ $br->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-3">
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Jam Masuk (Clock In)</label>
                <input type="time" name="clock_in" value="{{ old('clock_in', $attendance->clock_in) }}"
                       class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-lg text-xs text-slate-900 focus:bg-white focus:ring-2 focus:ring-blue-600">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Jam Pulang (Clock Out)</label>
                <input type="time" name="clock_out" value="{{ old('clock_out', $attendance->clock_out) }}"
                       class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-lg text-xs text-slate-900 focus:bg-white focus:ring-2 focus:ring-blue-600">
            </div>
        </div>

        <div class="grid grid-cols-2 gap-3">
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Menit Terlambat</label>
                <input type="number" name="late_minutes" value="{{ old('late_minutes', $attendance->late_minutes) }}" min="0"
                       class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-lg text-xs text-slate-900 focus:bg-white focus:ring-2 focus:ring-blue-600">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Menit Pulang Awal</label>
                <input type="number" name="early_leave_minutes" value="{{ old('early_leave_minutes', $attendance->early_leave_minutes) }}" min="0"
                       class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-lg text-xs text-slate-900 focus:bg-white focus:ring-2 focus:ring-blue-600">
            </div>
        </div>

        <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1">Catatan</label>
            <textarea name="notes" rows="2"
                      class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-lg text-xs text-slate-900 focus:bg-white focus:ring-2 focus:ring-blue-600">{{ old('notes', $attendance->notes) }}</textarea>
        </div>

        <div class="pt-3 border-t border-slate-100 flex items-center justify-end space-x-2">
            <a href="{{ route('admin.attendance.index', ['date' => $attendance->date->format('Y-m-d')]) }}" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-lg transition">
                Batal
            </a>
            <button type="submit" class="px-4 py-2 text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 rounded-lg transition shadow-md shadow-blue-600/30">
                Simpan Perubahan
            </button>
        </div>
    </form>

</div>
@endsection
