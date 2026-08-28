@extends('layouts.admin', ['title' => 'Master Jabatan', 'header' => 'Master Jabatan & Posisi'])

@section('content')
<div class="space-y-6">

    <!-- Header & Action -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <h1 class="text-xl font-bold text-slate-900 tracking-tight">Master Jabatan (Positions)</h1>
            <p class="text-xs text-slate-500 mt-0.5">Kelola penamaan posisi, jenjang karier, dan divisi naungan</p>
        </div>
        <div class="flex items-center space-x-2">
            <a href="{{ route('admin.departments.index') }}" class="px-3.5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs rounded-xl transition flex items-center space-x-1.5 border border-slate-200">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                <span>Kelola Divisi</span>
            </a>
            <a href="{{ route('admin.positions.create') }}" class="px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs rounded-xl shadow-md shadow-blue-600/30 transition flex items-center space-x-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                <span>Tambah Jabatan Baru</span>
            </a>
        </div>
    </div>

    <!-- Filter & Search Bar -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-3">
        <form method="GET" action="{{ route('admin.positions.index') }}" class="flex flex-wrap items-center gap-2 w-full md:w-auto">
            <div class="min-w-[220px]">
                <input type="text" name="search" value="{{ request('search') }}" 
                       placeholder="Cari nama jabatan..." 
                       class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-lg text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-600">
            </div>

            <select name="department_id" class="px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-600">
                <option value="">Semua Divisi</option>
                @foreach($departments as $dept)
                    <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>
                        {{ $dept->name }}
                    </option>
                @endforeach
            </select>

            <select name="level" class="px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-600">
                <option value="">Semua Level</option>
                @foreach($levels as $lvl)
                    <option value="{{ $lvl }}" {{ request('level') == $lvl ? 'selected' : '' }}>
                        Level: {{ $lvl }}
                    </option>
                @endforeach
            </select>

            <button type="submit" class="px-3.5 py-2 bg-slate-800 hover:bg-slate-900 text-white font-semibold text-xs rounded-lg transition">
                Filter
            </button>

            @if(request()->hasAny(['search', 'department_id', 'level']))
                <a href="{{ route('admin.positions.index') }}" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs rounded-lg transition">
                    Reset
                </a>
            @endif
        </form>

        <span class="text-xs text-slate-500">Total: <strong>{{ $positions->total() }}</strong> Jabatan</span>
    </div>

    <!-- Positions Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-500 font-semibold border-b border-slate-200/80">
                    <tr>
                        <th class="px-4 py-3.5">Nama Jabatan</th>
                        <th class="px-4 py-3.5">Divisi / Departemen</th>
                        <th class="px-4 py-3.5">Level Hierarki</th>
                        <th class="px-4 py-3.5">Karyawan Terhubung</th>
                        <th class="px-4 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($positions as $pos)
                        <tr class="hover:bg-slate-50/60 transition">
                            <td class="px-4 py-3.5 font-bold text-slate-900 text-sm">
                                {{ $pos->name }}
                            </td>
                            <td class="px-4 py-3.5">
                                @if($pos->department)
                                    <span class="font-semibold text-slate-800">{{ $pos->department->name }}</span>
                                    <span class="text-[10px] text-slate-400 font-mono">({{ $pos->department->code ?? '-' }})</span>
                                @else
                                    <span class="text-slate-400 italic">Lintas Divisi / General</span>
                                @endif
                            </td>
                            <td class="px-4 py-3.5">
                                @php
                                    $levelBadge = match(strtolower($pos->level)) {
                                        'director' => 'bg-purple-100 text-purple-800 border-purple-200',
                                        'manager' => 'bg-blue-100 text-blue-800 border-blue-200',
                                        'supervisor' => 'bg-amber-100 text-amber-800 border-amber-200',
                                        'intern' => 'bg-slate-100 text-slate-600 border-slate-200',
                                        default => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                                    };
                                @endphp
                                <span class="px-2 py-0.5 text-[11px] font-bold rounded-md border {{ $levelBadge }}">
                                    {{ $pos->level }}
                                </span>
                            </td>
                            <td class="px-4 py-3.5">
                                <span class="px-2 py-0.5 text-[11px] font-semibold rounded-full bg-slate-100 text-slate-700">
                                    {{ $pos->employees_count }} Orang
                                </span>
                            </td>
                            <td class="px-4 py-3.5 text-right space-x-1">
                                <a href="{{ route('admin.positions.edit', $pos->id) }}" class="px-2.5 py-1 text-[11px] font-semibold text-blue-600 hover:bg-blue-50 rounded transition">
                                    Edit
                                </a>
                                <form action="{{ route('admin.positions.destroy', $pos->id) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" 
                                            @click="$dispatch('open-alert-dialog', {
                                                title: 'Hapus Jabatan',
                                                description: 'Apakah Anda yakin ingin menghapus jabatan \'{{ addslashes($pos->name) }}\'? Tindakan ini tidak dapat dilakukan jika masih ada karyawan aktif dengan posisi ini.',
                                                confirmText: 'Ya, Hapus Jabatan',
                                                type: 'destructive',
                                                form: $el.closest('form')
                                            })"
                                            class="px-2.5 py-1 text-[11px] font-semibold text-rose-600 hover:bg-rose-50 rounded transition cursor-pointer">
                                        Hapus
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="p-8 text-center text-xs text-slate-400">
                                Belum ada data jabatan yang sesuai.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($positions->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $positions->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
