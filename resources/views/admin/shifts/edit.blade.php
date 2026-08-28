@extends('layouts.admin', ['title' => 'Edit Master Shift', 'header' => 'Edit Master Shift'])

@section('content')
<div class="max-w-xl mx-auto space-y-6">

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-slate-900 tracking-tight">Edit Shift: {{ $shift->name }}</h1>
            <p class="text-xs text-slate-500 mt-0.5">Perbarui jam kerja dan aturan toleransi keterlambatan</p>
        </div>
        <a href="{{ route('admin.shifts.index') }}" class="text-xs font-semibold text-slate-600 hover:text-slate-900">
            &larr; Kembali
        </a>
    </div>

    <form action="{{ route('admin.shifts.update', $shift->id) }}" method="POST" class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-sm space-y-4">
        @csrf
        @method('PUT')

        @if ($errors->any())
            <x-radix-alert type="destructive" title="Terjadi kesalahan pengisian form:">
                <ul class="list-disc list-inside space-y-0.5 mt-1 text-[11px]">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </x-radix-alert>
        @endif

        <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Shift</label>
            <input type="text" name="name" value="{{ old('name', $shift->name) }}" required
                   class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-lg text-xs text-slate-900 focus:bg-white focus:ring-2 focus:ring-blue-600 @error('name') border-rose-500 @enderror">
            @error('name') <p class="text-[11px] text-rose-600 mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="grid grid-cols-2 gap-3">
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Jam Masuk (Start)</label>
                <input type="time" name="start_time" value="{{ old('start_time', $shift->start_time) }}" required
                       class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-lg text-xs text-slate-900 focus:bg-white focus:ring-2 focus:ring-blue-600">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Jam Pulang (End)</label>
                <input type="time" name="end_time" value="{{ old('end_time', $shift->end_time) }}" required
                       class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-lg text-xs text-slate-900 focus:bg-white focus:ring-2 focus:ring-blue-600">
            </div>
        </div>

        <div class="grid grid-cols-2 gap-3">
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Toleransi Terlambat (Menit)</label>
                <input type="number" name="grace_period_minutes" value="{{ old('grace_period_minutes', $shift->grace_period_minutes) }}" required min="0" max="120"
                       class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-lg text-xs text-slate-900 focus:bg-white focus:ring-2 focus:ring-blue-600">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Shift Lintas Hari (Overnight)?</label>
                <select name="is_overnight" required class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-lg text-xs text-slate-900 focus:bg-white focus:ring-2 focus:ring-blue-600">
                    <option value="0" {{ !$shift->is_overnight ? 'selected' : '' }}>Tidak (Satu hari yang sama)</option>
                    <option value="1" {{ $shift->is_overnight ? 'selected' : '' }}>Ya (Melewati tengah malam / shift malam)</option>
                </select>
            </div>
        </div>

        <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1">Status Shift</label>
            <select name="is_active" required class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-lg text-xs text-slate-900 focus:bg-white focus:ring-2 focus:ring-blue-600">
                <option value="1" {{ $shift->is_active ? 'selected' : '' }}>Aktif</option>
                <option value="0" {{ !$shift->is_active ? 'selected' : '' }}>Non-Aktif</option>
            </select>
        </div>

        <div class="pt-3 border-t border-slate-100 flex items-center justify-end space-x-2">
            <a href="{{ route('admin.shifts.index') }}" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-lg transition">
                Batal
            </a>
            <button type="submit" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs rounded-xl transition shadow-md shadow-blue-600/30">
                Simpan Perubahan
            </button>
        </div>
    </form>

</div>
@endsection
