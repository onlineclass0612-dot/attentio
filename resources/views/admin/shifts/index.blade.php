@extends('layouts.admin', ['title' => 'Master Shift', 'header' => 'Master Shift Kerja'])

@section('content')
<div class="space-y-6">

    <!-- Header & Action -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-slate-900 tracking-tight">Master Shift Kerja</h1>
            <p class="text-xs text-slate-500 mt-0.5">Konfigurasi jam masuk, jam pulang, dan batas toleransi keterlambatan</p>
        </div>
        <a href="{{ route('admin.shifts.create') }}" class="px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs rounded-xl shadow-md shadow-blue-600/30 transition flex items-center space-x-1.5">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            <span>Tambah Shift Baru</span>
        </a>
    </div>

    <!-- Shifts Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-500 font-semibold border-b border-slate-200/80">
                    <tr>
                        <th class="px-4 py-3.5">Nama Shift</th>
                        <th class="px-4 py-3.5">Jam Masuk (Start)</th>
                        <th class="px-4 py-3.5">Jam Pulang (End)</th>
                        <th class="px-4 py-3.5">Toleransi Keterlambatan</th>
                        <th class="px-4 py-3.5">Tipe Shift</th>
                        <th class="px-4 py-3.5">Status</th>
                        <th class="px-4 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($shifts as $sh)
                        <tr class="hover:bg-slate-50/60 transition">
                            <td class="px-4 py-3.5 font-bold text-slate-900">
                                {{ $sh->name }}
                            </td>
                            <td class="px-4 py-3.5 font-mono text-slate-800 font-semibold">
                                {{ $sh->start_time }} WIB
                            </td>
                            <td class="px-4 py-3.5 font-mono text-slate-800 font-semibold">
                                {{ $sh->end_time }} WIB
                            </td>
                            <td class="px-4 py-3.5 font-mono text-amber-600 font-bold">
                                {{ $sh->grace_period_minutes }} Menit
                            </td>
                            <td class="px-4 py-3.5 text-slate-600">
                                @if($sh->is_overnight)
                                    <span class="px-2 py-0.5 text-[10px] font-bold rounded bg-purple-50 text-purple-700">Lintas Hari (Overnight)</span>
                                @else
                                    <span class="px-2 py-0.5 text-[10px] font-semibold text-slate-500">Reguler</span>
                                @endif
                            </td>
                            <td class="px-4 py-3.5">
                                @if($sh->is_active)
                                    <span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-emerald-100 text-emerald-800">Aktif</span>
                                @else
                                    <span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-rose-100 text-rose-800">Non-Aktif</span>
                                @endif
                            </td>
                            <td class="px-4 py-3.5 text-right space-x-1">
                                <a href="{{ route('admin.shifts.edit', $sh->id) }}" class="px-2.5 py-1 text-[11px] font-semibold text-blue-600 hover:bg-blue-50 rounded transition">
                                    Edit
                                </a>
                                <form action="{{ route('admin.shifts.destroy', $sh->id) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" 
                                            @click="$dispatch('open-alert-dialog', {
                                                title: 'Hapus Shift Kerja',
                                                description: 'Apakah Anda yakin ingin menghapus shift \'{{ addslashes($sh->name) }}\'? Data terkait dapat terpengaruh.',
                                                confirmText: 'Ya, Hapus Shift',
                                                type: 'destructive',
                                                form: $el.closest('form')
                                            })"
                                            class="px-2.5 py-1 text-[11px] font-semibold text-rose-600 hover:bg-rose-50 rounded transition cursor-pointer">
                                        Hapus
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
