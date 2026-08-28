@extends('layouts.app')

@section('content')
<div class="max-w-xl mx-auto space-y-6">

    <!-- Profile Header Card -->
    <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-sm flex items-center space-x-4">
        <img src="{{ $employee?->avatar_url ?? $user->avatar_url }}" 
             alt="{{ $user->name }}" 
             class="w-16 h-16 rounded-2xl object-cover ring-4 ring-blue-600/10 shadow-md">
        <div>
            <h1 class="text-lg font-bold text-slate-900 leading-snug">{{ $user->name }}</h1>
            <p class="text-xs text-slate-500 font-mono">NIK: {{ $employee->nik ?? '-' }}</p>
            <div class="mt-1 flex items-center space-x-2">
                <span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-blue-100 text-blue-800">
                    {{ $employee->position->name ?? 'Staff' }}
                </span>
                <span class="text-xs text-slate-400">&bull;</span>
                <span class="text-xs text-slate-600 font-medium">{{ $employee->department->name ?? 'Dept' }}</span>
            </div>
        </div>
    </div>

    <!-- Edit Profile Form -->
    <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-sm">
        <h2 class="text-sm font-bold text-slate-900 mb-4 pb-2 border-b border-slate-100">Pengaturan Akun & Kontak</h2>

        <form action="{{ route('employee.profile.update') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
            @csrf
            
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Email Terdaftar</label>
                <input type="email" value="{{ $user->email }}" disabled
                       class="w-full px-3.5 py-2.5 bg-slate-100 border border-slate-200 rounded-lg text-xs text-slate-500 cursor-not-allowed">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Nomor WhatsApp / Telepon</label>
                <input type="text" name="phone" value="{{ old('phone', $employee->phone ?? '') }}"
                       class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-lg text-xs text-slate-900 focus:bg-white focus:ring-2 focus:ring-blue-600"
                       placeholder="08xxxxxxxxxx">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Foto Profil Baru (Avatar)</label>
                <input type="file" name="avatar" accept="image/*"
                       class="w-full px-3 py-1.5 bg-slate-50 border border-slate-200 rounded-lg text-xs text-slate-500 file:mr-2 file:py-1 file:px-2 file:rounded file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
            </div>

            <div class="pt-4 border-t border-slate-100">
                <h3 class="text-xs font-bold text-slate-800 mb-3">Ubah Password (Kosongkan jika tidak ingin mengubah)</h3>
                
                <div class="space-y-3">
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-600 mb-1">Password Saat Ini</label>
                        <input type="password" name="current_password"
                               class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-lg text-xs text-slate-900 focus:bg-white focus:ring-2 focus:ring-blue-600">
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-600 mb-1">Password Baru</label>
                            <input type="password" name="new_password"
                                   class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-lg text-xs text-slate-900 focus:bg-white focus:ring-2 focus:ring-blue-600">
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-600 mb-1">Konfirmasi Password Baru</label>
                            <input type="password" name="new_password_confirmation"
                                   class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-lg text-xs text-slate-900 focus:bg-white focus:ring-2 focus:ring-blue-600">
                        </div>
                    </div>
                </div>
            </div>

            <div class="pt-3">
                <button type="submit" class="w-full py-2.5 px-4 bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs rounded-lg transition shadow-md shadow-blue-600/30">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
