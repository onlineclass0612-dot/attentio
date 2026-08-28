<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="Attention OS - Portal Layanan Mandiri Karyawan (Employee Self Service) untuk presensi GPS, cuti, lembur, dan absensi terpadu.">
    <meta name="robots" content="noindex, nofollow">
    <meta name="theme-color" content="#2563eb">
    <title>{{ $title ?? 'Attention OS' }} — HR Attendance System</title>
    
    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full bg-[#f8f9ff] text-slate-800 antialiased flex flex-col font-sans selection:bg-blue-600 selection:text-white pb-20 md:pb-6">

    <!-- 1. Top App Bar (Mobile & Desktop Header) -->
    <header class="sticky top-0 z-40 bg-white/90 backdrop-blur-md border-b border-slate-200/80 px-4 py-3">
        <div class="max-w-5xl mx-auto flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <a href="{{ route('employee.profile.show') }}" class="relative group" aria-label="Buka Profil Pengguna">
                    <img src="{{ Auth::user()->avatar_url }}" 
                         alt="Foto Profil {{ Auth::user()->name }}" 
                         class="w-10 h-10 rounded-full object-cover ring-2 ring-blue-600/20 group-hover:ring-blue-600 transition shadow-sm">
                    <span class="absolute bottom-0 right-0 w-3 h-3 bg-emerald-500 border-2 border-white rounded-full"></span>
                </a>
                <div>
                    <div class="flex items-center space-x-1.5">
                        <span class="font-bold text-slate-900 tracking-tight text-lg">Attention</span>
                        <span class="px-1.5 py-0.5 text-[10px] font-semibold bg-blue-50 text-blue-700 rounded border border-blue-200/60 uppercase">ESS</span>
                    </div>
                    <p class="text-xs text-slate-500 font-medium truncate max-w-[160px] sm:max-w-xs">{{ Auth::user()->name }}</p>
                </div>
            </div>

            <div class="flex items-center space-x-2">
                @if(Auth::user()->hasRole(['Super Admin', 'HR Manager', 'Supervisor']))
                    <a href="{{ route('admin.dashboard') }}" class="hidden sm:inline-flex items-center px-3 py-1.5 text-xs font-semibold text-blue-700 bg-blue-50 hover:bg-blue-100 rounded-lg border border-blue-200 transition">
                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                        Admin Portal
                    </a>
                @endif

                <!-- Logout Button -->
                <form action="{{ route('logout') }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="p-2 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition cursor-pointer" title="Logout" aria-label="Keluar dari Akun">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                    </button>
                </form>
            </div>
        </div>
    </header>

    <!-- 2. Flash Messages Alert (Radix UI Callout) -->
    <div class="max-w-5xl mx-auto w-full px-4 pt-4">
        <x-radix-flash />
    </div>

    <!-- 3. Main Content -->
    <main class="flex-1 max-w-5xl mx-auto w-full px-4 py-2">
        @yield('content')
    </main>

    <!-- 4. Bottom Navigation (Mobile ESS Dock) -->
    <nav class="fixed bottom-0 left-0 right-0 z-50 bg-white/95 backdrop-blur-lg border-t border-slate-200 shadow-[0_-4px_20px_rgba(0,0,0,0.05)] md:hidden">
        <div class="grid grid-cols-5 h-16 max-w-md mx-auto items-center px-1">
            
            <!-- Dashboard -->
            <a href="{{ route('employee.dashboard') }}" class="flex flex-col items-center justify-center py-1 {{ request()->routeIs('employee.dashboard') ? 'text-blue-600 font-semibold' : 'text-slate-500 hover:text-slate-900' }}" aria-label="Halaman Beranda">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                <span class="text-[10px] mt-1">Beranda</span>
            </a>

            <!-- Clock-In (Prominent Floating Icon) -->
            <a href="{{ route('employee.attendance.create') }}" class="flex flex-col items-center justify-center -mt-5" aria-label="Buka Halaman Presensi Absen">
                <div class="w-12 h-12 rounded-full bg-blue-600 text-white flex items-center justify-center shadow-lg shadow-blue-600/40 ring-4 ring-white hover:bg-blue-700 transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <span class="text-[10px] font-semibold text-blue-600 mt-0.5">Absen</span>
            </a>

            <!-- History -->
            <a href="{{ route('employee.attendance.history') }}" class="flex flex-col items-center justify-center py-1 {{ request()->routeIs('employee.attendance.history') ? 'text-blue-600 font-semibold' : 'text-slate-500 hover:text-slate-900' }}" aria-label="Riwayat Presensi">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                <span class="text-[10px] mt-1">Riwayat</span>
            </a>

            <!-- Leave -->
            <a href="{{ route('employee.leave.index') }}" class="flex flex-col items-center justify-center py-1 {{ request()->routeIs('employee.leave.*') ? 'text-blue-600 font-semibold' : 'text-slate-500 hover:text-slate-900' }}" aria-label="Manajemen Cuti dan Izin">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                <span class="text-[10px] mt-1">Cuti</span>
            </a>

            <!-- Profile -->
            <a href="{{ route('employee.profile.show') }}" class="flex flex-col items-center justify-center py-1 {{ request()->routeIs('employee.profile.*') ? 'text-blue-600 font-semibold' : 'text-slate-500 hover:text-slate-900' }}" aria-label="Profil Karyawan">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                <span class="text-[10px] mt-1">Profil</span>
            </a>
        </div>
    </nav>

    <!-- Global Radix Alert Dialog for Delete & Confirmations -->
    <x-radix-alert-dialog />

    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    @stack('scripts')
</body>
</html>
