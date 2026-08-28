@extends('layouts.admin', ['title' => 'Tambah Jabatan', 'header' => 'Tambah Jabatan Baru'])

@section('content')
<div class="max-w-2xl mx-auto space-y-6">

    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-slate-900 tracking-tight">Tambah Jabatan Baru</h1>
            <p class="text-xs text-slate-500 mt-0.5">Daftarkan nama jabatan dan kelompokkan ke divisi yang sesuai</p>
        </div>
        <a href="{{ route('admin.positions.index') }}" class="px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs rounded-xl transition flex items-center space-x-1.5 border border-slate-200">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            <span>Kembali</span>
        </a>
    </div>

    <!-- Form -->
    <form action="{{ route('admin.positions.store') }}" method="POST" class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-sm space-y-4">
        @csrf

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
            <label for="name" class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1.5">
                Nama Posisi / Jabatan <span class="text-rose-500">*</span>
            </label>
            <input type="text" name="name" id="name" value="{{ old('name') }}" required
                   placeholder="Contoh: Senior Frontend Engineer, HR Specialist, Finance Analyst"
                   class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-lg text-xs text-slate-900 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent transition @error('name') border-rose-500 @enderror">
            @error('name')
                <p class="text-[11px] text-rose-600 mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label for="department_id" class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1.5">
                    Divisi / Departemen <span class="text-rose-500">*</span>
                </label>
                <select name="department_id" id="department_id" required
                        class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-lg text-xs text-slate-900 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent transition @error('department_id') border-rose-500 @enderror">
                    <option value="">-- Pilih Divisi --</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept->id }}" {{ old('department_id') == $dept->id ? 'selected' : '' }}>
                            {{ $dept->name }} ({{ $dept->code ?? '-' }})
                        </option>
                    @endforeach
                </select>
                @error('department_id')
                    <p class="text-[11px] text-rose-600 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="level" class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1.5">
                    Level Hierarki <span class="text-rose-500">*</span>
                </label>
                <select name="level" id="level" required
                        class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-lg text-xs text-slate-900 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent transition @error('level') border-rose-500 @enderror">
                    @foreach($levels as $lvl)
                        <option value="{{ $lvl }}" {{ old('level', 'Staff') == $lvl ? 'selected' : '' }}>
                            {{ $lvl }}
                        </option>
                    @endforeach
                </select>
                @error('level')
                    <p class="text-[11px] text-rose-600 mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="pt-4 border-t border-slate-100 flex items-center justify-end space-x-2.5">
            <a href="{{ route('admin.positions.index') }}" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs rounded-xl transition">
                Batal
            </a>
            <button type="submit" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs rounded-xl shadow-md shadow-blue-600/30 transition flex items-center space-x-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                <span>Simpan Jabatan</span>
            </button>
        </div>
    </form>

</div>
@endsection
