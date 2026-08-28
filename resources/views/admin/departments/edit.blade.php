@extends('layouts.admin', ['title' => 'Edit Divisi', 'header' => 'Edit Data Divisi'])

@section('content')
<div class="max-w-2xl mx-auto space-y-6">

    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-slate-900 tracking-tight">Edit Data Divisi</h1>
            <p class="text-xs text-slate-500 mt-0.5">Perbarui nama atau keterangan divisi {{ $department->name }}</p>
        </div>
        <a href="{{ route('admin.departments.index') }}" class="px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs rounded-xl transition flex items-center space-x-1.5 border border-slate-200">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            <span>Kembali</span>
        </a>
    </div>

    <!-- Form -->
    <form action="{{ route('admin.departments.update', $department->id) }}" method="POST" class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-sm space-y-4">
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
            <label for="name" class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1.5">
                Nama Divisi / Departemen <span class="text-rose-500">*</span>
            </label>
            <input type="text" name="name" id="name" value="{{ old('name', $department->name) }}" required
                   class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-lg text-xs text-slate-900 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent transition @error('name') border-rose-500 @enderror">
            @error('name')
                <p class="text-[11px] text-rose-600 mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="code" class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1.5">
                Kode Singkatan Divisi
            </label>
            <input type="text" name="code" id="code" value="{{ old('code', $department->code) }}"
                   class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-lg text-xs text-slate-900 font-mono uppercase focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent transition @error('code') border-rose-500 @enderror">
            @error('code')
                <p class="text-[11px] text-rose-600 mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="description" class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1.5">
                Deskripsi / Tanggung Jawab Unit
            </label>
            <textarea name="description" id="description" rows="3"
                      class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-lg text-xs text-slate-900 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent transition @error('description') border-rose-500 @enderror">{{ old('description', $department->description) }}</textarea>
            @error('description')
                <p class="text-[11px] text-rose-600 mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="pt-4 border-t border-slate-100 flex items-center justify-end space-x-2.5">
            <a href="{{ route('admin.departments.index') }}" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs rounded-xl transition">
                Batal
            </a>
            <button type="submit" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs rounded-xl shadow-md shadow-blue-600/30 transition flex items-center space-x-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                <span>Simpan Perubahan</span>
            </button>
        </div>
    </form>

</div>
@endsection
