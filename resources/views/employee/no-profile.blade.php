@extends('layouts.app')

@section('content')
<div class="max-w-md mx-auto my-12 bg-white p-8 rounded-2xl border border-slate-200/80 shadow-sm text-center">
    <div class="w-16 h-16 bg-blue-50 text-blue-600 rounded-full flex items-center justify-center mx-auto mb-4">
        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
    </div>
    <h1 class="text-lg font-bold text-slate-900">Profil Karyawan Belum Terhubung</h1>
    <p class="text-xs text-slate-500 mt-2">
        Akun pengguna Anda belum dikaitkan dengan profil data karyawan oleh Tim HR. Silakan hubungi HR Administrator untuk melengkapi data Anda.
    </p>

    @if(Auth::user()->hasRole(['Super Admin', 'HR Manager', 'Supervisor']))
        <div class="mt-6">
            <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center px-4 py-2.5 bg-blue-600 text-white font-semibold text-xs rounded-lg shadow-md shadow-blue-600/30">
                Masuk ke Admin Portal
            </a>
        </div>
    @endif
</div>
@endsection
