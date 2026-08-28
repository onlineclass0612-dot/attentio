@extends('layouts.admin', ['title' => 'Kantor & Geofence', 'header' => 'Manajemen Lokasi Kantor & Geofence'])

@section('content')
<div class="space-y-6">

    <!-- Header & Action -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-slate-900 tracking-tight">Lokasi Kantor & Geofencing</h1>
            <p class="text-xs text-slate-500 mt-0.5">Konfigurasi koordinat GPS dan radius batas presensi kehadiran</p>
        </div>
        <a href="{{ route('admin.branches.create') }}" class="px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs rounded-xl shadow-md shadow-blue-600/30 transition flex items-center space-x-1.5">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            <span>Tambah Lokasi Baru</span>
        </a>
    </div>

    <!-- Branches Grid Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        @foreach($branches as $branch)
            <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-sm space-y-4 flex flex-col justify-between">
                <div>
                    <div class="flex items-start justify-between">
                        <div>
                            <span class="px-2 py-0.5 text-[10px] font-bold rounded bg-blue-50 text-blue-700 border border-blue-200 font-mono">{{ $branch->code }}</span>
                            <h2 class="text-base font-bold text-slate-900 mt-1">{{ $branch->name }}</h2>
                        </div>
                        @if($branch->is_active)
                            <span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-emerald-100 text-emerald-800">Aktif</span>
                        @else
                            <span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-rose-100 text-rose-800">Non-Aktif</span>
                        @endif
                    </div>
                    
                    <p class="text-xs text-slate-500 mt-2">{{ $branch->address ?? 'Alamat belum diatur' }}</p>

                    <div class="mt-4 pt-3 border-t border-slate-100 grid grid-cols-2 gap-2 text-xs">
                        <div class="p-2.5 bg-slate-50 rounded-lg">
                            <span class="text-[10px] text-slate-400 block font-semibold uppercase">Koordinat GPS</span>
                            <span class="font-mono text-slate-800 font-medium text-[11px] block truncate">{{ $branch->latitude }}, {{ $branch->longitude }}</span>
                        </div>
                        <div class="p-2.5 bg-slate-50 rounded-lg">
                            <span class="text-[10px] text-slate-400 block font-semibold uppercase">Radius Batas</span>
                            <span class="font-mono text-blue-600 font-bold text-[11px] block">{{ $branch->radius_meters }} Meter</span>
                        </div>
                    </div>
                </div>

                <div class="pt-3 border-t border-slate-100 flex items-center justify-between">
                    <span class="text-xs text-slate-500 font-medium">{{ $branch->employees_count ?? 0 }} Karyawan terhubung</span>
                    
                    <div class="space-x-2">
                        <a href="{{ route('admin.branches.edit', $branch->id) }}" class="px-3 py-1.5 text-xs font-semibold text-blue-600 hover:bg-blue-50 rounded-lg transition">
                            Edit
                        </a>
                        @if(($branch->employees_count ?? 0) === 0)
                            <form action="{{ route('admin.branches.destroy', $branch->id) }}" method="POST" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="button" 
                                        @click="$dispatch('open-alert-dialog', {
                                            title: 'Hapus Lokasi Kantor',
                                            description: 'Apakah Anda yakin ingin menghapus kantor \'{{ addslashes($branch->name) }}\' ({{ $branch->code }})? Konfigurasi koordinat dan geofence lokasi ini akan dihapus.',
                                            confirmText: 'Ya, Hapus Kantor',
                                            type: 'destructive',
                                            form: $el.closest('form')
                                        })"
                                        class="px-3 py-1.5 text-xs font-semibold text-rose-600 hover:bg-rose-50 rounded-lg transition cursor-pointer">
                                    Hapus
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>

</div>
@endsection
