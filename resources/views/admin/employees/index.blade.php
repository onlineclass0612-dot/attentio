@extends('layouts.admin', ['title' => 'Data Karyawan', 'header' => 'Manajemen Data Karyawan'])

@section('content')
<div class="space-y-6">

    <!-- Header & Action Button -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-slate-900 tracking-tight">Daftar Karyawan</h1>
            <p class="text-xs text-slate-500 mt-0.5">Kelola data seluruh profil pengguna dan penugasan shift</p>
        </div>
        @hasanyrole('Super Admin|HR Manager')
        <a href="{{ route('admin.employees.create') }}" class="px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs rounded-xl shadow-md shadow-blue-600/30 transition flex items-center space-x-1.5 self-start sm:self-auto">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            <span>Tambah Karyawan</span>
        </a>
        @endhasanyrole
    </div>

    <!-- Filter & Search Bar -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm">
        <form method="GET" action="{{ route('admin.employees.index') }}" class="grid grid-cols-1 sm:grid-cols-4 gap-3">
            <div class="sm:col-span-2">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama, NIK, atau email..."
                       class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-lg text-xs text-slate-900 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-600">
            </div>
            <div>
                <select name="department_id" class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-lg text-xs text-slate-900 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-600">
                    <option value="">Semua Departemen</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <button type="submit" class="w-full py-2 px-4 bg-slate-900 hover:bg-slate-800 text-white font-semibold text-xs rounded-lg transition">
                    Terapkan Filter
                </button>
            </div>
        </form>
    </div>

    <!-- Employees Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-500 font-semibold border-b border-slate-200/80">
                    <tr>
                        <th class="px-4 py-3.5">Karyawan</th>
                        <th class="px-4 py-3.5">NIK</th>
                        <th class="px-4 py-3.5">Jabatan & Dept</th>
                        <th class="px-4 py-3.5">Lokasi Kantor</th>
                        <th class="px-4 py-3.5">Role Akses</th>
                        <th class="px-4 py-3.5">Status</th>
                        <th class="px-4 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($employees as $emp)
                        <tr class="hover:bg-slate-50/60 transition">
                            <td class="px-4 py-3.5">
                                <div class="flex items-center space-x-3">
                                    <img src="{{ $emp->avatar_url }}" class="w-8 h-8 rounded-full object-cover ring-1 ring-slate-200">
                                    <div>
                                        <p class="font-bold text-slate-900">{{ $emp->user->name ?? 'User' }}</p>
                                        <p class="text-[11px] text-slate-400">{{ $emp->user->email ?? '-' }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3.5 font-mono text-slate-700 font-semibold">
                                {{ $emp->nik }}
                            </td>
                            <td class="px-4 py-3.5">
                                <p class="font-semibold text-slate-800">{{ $emp->position->name ?? '-' }}</p>
                                <p class="text-[10px] text-slate-400">{{ $emp->department->name ?? '-' }}</p>
                            </td>
                            <td class="px-4 py-3.5 text-slate-600">
                                {{ $emp->branch->name ?? '-' }}
                            </td>
                            <td class="px-4 py-3.5">
                                <span class="px-2 py-0.5 text-[10px] font-bold rounded bg-blue-50 text-blue-700 border border-blue-200">
                                    {{ $emp->user->getRoleNames()->first() ?? 'Employee' }}
                                </span>
                            </td>
                            <td class="px-4 py-3.5">
                                @if($emp->is_active)
                                    <span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-emerald-100 text-emerald-800">Aktif</span>
                                @else
                                    <span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-rose-100 text-rose-800">Non-Aktif</span>
                                @endif
                            </td>
                            <td class="px-4 py-3.5 text-right space-x-2">
                                @hasanyrole('Super Admin|HR Manager')
                                <a href="{{ route('admin.employees.edit', $emp->id) }}" class="px-2.5 py-1 text-[11px] font-semibold text-blue-600 hover:text-blue-800 bg-blue-50 hover:bg-blue-100 rounded transition">
                                    Edit
                                </a>
                                <form action="{{ route('admin.employees.destroy', $emp->id) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" 
                                            @click="$dispatch('open-alert-dialog', {
                                                title: 'Hapus Data Karyawan',
                                                description: 'Apakah Anda yakin ingin menghapus data karyawan \'{{ addslashes($emp->user->name ?? 'Karyawan') }}\' (NIK: {{ $emp->nik }})? Akun login dan riwayat terkait akan dihapus.',
                                                confirmText: 'Ya, Hapus Karyawan',
                                                type: 'destructive',
                                                form: $el.closest('form')
                                            })"
                                            class="px-2.5 py-1 text-[11px] font-semibold text-rose-600 hover:text-rose-800 bg-rose-50 hover:bg-rose-100 rounded transition cursor-pointer">
                                        Hapus
                                    </button>
                                </form>
                                @else
                                <span class="text-[11px] text-slate-400 font-medium">Hanya Lihat</span>
                                @endhasanyrole
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="p-8 text-center text-xs text-slate-400">
                                Belum ada data karyawan yang cocok.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($employees->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $employees->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
