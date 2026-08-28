@extends('layouts.app')

@section('content')
<div class="min-h-[75vh] flex items-center justify-center p-4">
    <div class="max-w-md w-full bg-white rounded-2xl border border-slate-200/90 shadow-xl shadow-slate-200/50 p-6 sm:p-8 text-center">
        
        <!-- Status Icon -->
        <div class="w-16 h-16 bg-rose-50 text-rose-600 rounded-2xl flex items-center justify-center mx-auto mb-5 border border-rose-100 shadow-sm">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path>
            </svg>
        </div>

        <!-- Badge & Title -->
        <div class="inline-flex items-center space-x-1.5 px-3 py-1 bg-rose-50 border border-rose-200 rounded-full text-rose-700 text-[11px] font-bold uppercase tracking-wider mb-2.5">
            <span class="w-2 h-2 rounded-full bg-rose-500"></span>
            <span>Status Kepegawaian Non-Aktif</span>
        </div>

        <h1 class="text-xl font-bold text-slate-900 tracking-tight">Akses Portal Dinonaktifkan</h1>
        <p class="text-xs text-slate-500 mt-1.5 leading-relaxed">
            Status kepegawaian Anda saat ini sedang dinonaktifkan dalam sistem database perusahaan.
        </p>

        <!-- Employee Info Summary -->
        @if(Auth::user()->employee)
            <div class="my-5 p-4 bg-slate-50/80 border border-slate-200/70 rounded-xl text-left divide-y divide-slate-200/50 text-xs">
                <div class="flex items-center justify-between py-1.5 first:pt-0">
                    <span class="text-slate-400">Nama Pegawai</span>
                    <strong class="text-slate-800 font-semibold">{{ Auth::user()->name }}</strong>
                </div>
                <div class="flex items-center justify-between py-1.5">
                    <span class="text-slate-400">NIK</span>
                    <span class="font-mono text-slate-700 font-medium">{{ Auth::user()->employee->nik }}</span>
                </div>
                <div class="flex items-center justify-between py-1.5">
                    <span class="text-slate-400">Departemen</span>
                    <span class="text-slate-700 font-medium">{{ Auth::user()->employee->department->name ?? '-' }}</span>
                </div>
                <div class="flex items-center justify-between py-1.5 last:pb-0">
                    <span class="text-slate-400">Status</span>
                    <span class="px-2 py-0.5 bg-rose-100 text-rose-800 font-bold text-[10px] rounded-full">Non-Aktif</span>
                </div>
            </div>
        @endif

        <!-- Help/Contact Note -->
        <p class="text-xs text-slate-500 bg-amber-50/60 border border-amber-200/70 rounded-xl p-3 text-left leading-relaxed text-amber-900">
            Akses absensi, cuti, dan lembur dinonaktifkan. Silakan hubungi <strong>HR Administrator</strong> jika Anda membutuhkan informasi lebih lanjut.
        </p>

        <!-- Action: Logout Button -->
        <div class="mt-6">
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="w-full py-2.5 px-4 bg-slate-900 hover:bg-slate-800 text-white font-semibold text-xs rounded-xl shadow-md shadow-slate-900/10 transition flex items-center justify-center space-x-2 cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                    </svg>
                    <span>Keluar dari Akun (Logout)</span>
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
