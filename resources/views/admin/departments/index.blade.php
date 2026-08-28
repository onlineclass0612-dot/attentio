@extends('layouts.admin', ['title' => 'Master Divisi', 'header' => 'Master Divisi Organisasi'])

@section('content')
<div class="space-y-6">

    <!-- Header & Action -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <h1 class="text-xl font-bold text-slate-900 tracking-tight">Master Divisi / Departemen</h1>
            <p class="text-xs text-slate-500 mt-0.5">Kelola struktur unit kerja dan departemen operasional perusahaan</p>
        </div>
        <div class="flex items-center space-x-2">
            <a href="{{ route('admin.positions.index') }}" class="px-3.5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs rounded-xl transition flex items-center space-x-1.5 border border-slate-200">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                <span>Kelola Jabatan</span>
            </a>
            <a href="{{ route('admin.departments.create') }}" class="px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs rounded-xl shadow-md shadow-blue-600/30 transition flex items-center space-x-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                <span>Tambah Divisi Baru</span>
            </a>
        </div>
    </div>

    <!-- Filter & Search -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm flex items-center justify-between">
        <form method="GET" action="{{ route('admin.departments.index') }}" class="flex items-center space-x-2 w-full max-w-sm">
            <div class="w-full">
                <input type="text" name="search" value="{{ request('search') }}" 
                       placeholder="Cari nama atau kode divisi..." 
                       class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-lg text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-600">
            </div>
            <button type="submit" class="px-3.5 py-2 bg-slate-800 hover:bg-slate-900 text-white font-semibold text-xs rounded-lg transition">
                Cari
            </button>
            @if(request('search'))
                <a href="{{ route('admin.departments.index') }}" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs rounded-lg transition">
                    Reset
                </a>
            @endif
        </form>
        <span class="text-xs text-slate-500 hidden sm:inline">Total: <strong>{{ $departments->total() }}</strong> Divisi</span>
    </div>

    <!-- Departments Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-500 font-semibold border-b border-slate-200/80">
                    <tr>
                        <th class="px-4 py-3.5">Kode</th>
                        <th class="px-4 py-3.5">Nama Divisi</th>
                        <th class="px-4 py-3.5">Deskripsi</th>
                        <th class="px-4 py-3.5">Jumlah Jabatan</th>
                        <th class="px-4 py-3.5">Jumlah Karyawan</th>
                        <th class="px-4 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($departments as $dept)
                        <tr class="hover:bg-slate-50/60 transition">
                            <td class="px-4 py-3.5">
                                <span class="px-2 py-0.5 text-[11px] font-bold rounded bg-blue-50 text-blue-700 border border-blue-200/60 font-mono">
                                    {{ $dept->code ?? '-' }}
                                </span>
                            </td>
                            <td class="px-4 py-3.5 font-bold text-slate-900 text-sm">
                                {{ $dept->name }}
                            </td>
                            <td class="px-4 py-3.5 text-slate-500 max-w-xs truncate">
                                {{ $dept->description ?? '-' }}
                            </td>
                            <td class="px-4 py-3.5">
                                <a href="{{ route('admin.positions.index', ['department_id' => $dept->id]) }}" class="inline-flex items-center space-x-1 text-slate-700 hover:text-blue-600 font-semibold">
                                    <span>{{ $dept->positions_count }} Jabatan</span>
                                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                                </a>
                            </td>
                            <td class="px-4 py-3.5">
                                <span class="px-2 py-0.5 text-[11px] font-semibold rounded-full bg-slate-100 text-slate-700">
                                    {{ $dept->employees_count }} Anggota
                                </span>
                            </td>
                            <td class="px-4 py-3.5 text-right space-x-1">
                                <a href="{{ route('admin.departments.edit', $dept->id) }}" class="px-2.5 py-1 text-[11px] font-semibold text-blue-600 hover:bg-blue-50 rounded transition">
                                    Edit
                                </a>
                                <form action="{{ route('admin.departments.destroy', $dept->id) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" 
                                            @click="$dispatch('open-alert-dialog', {
                                                title: 'Hapus Divisi Organisasi',
                                                description: 'Apakah Anda yakin ingin menghapus divisi \'{{ addslashes($dept->name) }}\'? Tindakan ini tidak dapat dilakukan jika masih memiliki data jabatan atau karyawan terkait.',
                                                confirmText: 'Ya, Hapus Divisi',
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
                            <td colspan="6" class="p-8 text-center text-xs text-slate-400">
                                Belum ada data divisi yang terdaftar.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($departments->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $departments->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
