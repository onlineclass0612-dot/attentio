<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk — Attention OS</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full bg-[#f8f9ff] flex items-center justify-center p-4 selection:bg-blue-600 selection:text-white">

    <div class="w-full max-w-md bg-white rounded-2xl shadow-xl shadow-slate-200/50 border border-slate-200/80 p-8">
        
        <!-- Header -->
        <div class="text-center mb-8">
            <div class="w-12 h-12 bg-blue-600 text-white rounded-xl flex items-center justify-center font-black text-2xl mx-auto mb-3 shadow-lg shadow-blue-600/30">
                A
            </div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Attention OS</h1>
            <p class="text-sm text-slate-500 mt-1">HR & Attendance Management System</p>
        </div>

        <div class="mb-5">
            <x-radix-flash />
        </div>

        <!-- Login Form -->
        <form action="{{ route('login') }}" method="POST" class="space-y-4" id="loginForm">
            @csrf
            
            <div>
                <label for="email" class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1.5">Alamat Email</label>
                <input type="email" name="email" id="email" value="{{ old('email', 'admin@attention.test') }}" required
                       class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-lg text-sm text-slate-900 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent transition @error('email') border-rose-500 @enderror"
                       placeholder="nama@perusahaan.com">
                @error('email')
                    <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <div class="flex items-center justify-between mb-1.5">
                    <label for="password" class="block text-xs font-semibold uppercase tracking-wider text-slate-600">Kata Sandi</label>
                </div>
                <input type="password" name="password" id="password" value="password" required
                       class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-lg text-sm text-slate-900 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent transition @error('password') border-rose-500 @enderror"
                       placeholder="••••••••">
                @error('password')
                    <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center justify-between pt-1">
                <label class="flex items-center space-x-2 text-xs text-slate-600 cursor-pointer select-none">
                    <input type="checkbox" name="remember" class="w-4 h-4 rounded text-blue-600 border-slate-300 focus:ring-blue-500">
                    <span>Ingat saya</span>
                </label>
            </div>

            <button type="submit" class="w-full py-2.5 px-4 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg shadow-md shadow-blue-600/30 transition duration-150 ease-in-out flex items-center justify-center">
                <span>Masuk ke Akun</span>
                <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
            </button>
        </form>

        <!-- Demo Accounts Quick Fill (4 Core Roles) -->
        <div class="mt-6 pt-5 border-t border-slate-200/80 space-y-2.5">
            <p class="text-[11px] font-bold text-slate-500 uppercase tracking-wider text-center">Akun Demo (Klik untuk Isi Cepat)</p>

            <div class="grid grid-cols-2 gap-2.5">
                <!-- 1. Super Admin -->
                <button type="button" onclick="fillDemo('admin@attention.test', 'password')" class="p-3 bg-slate-50 hover:bg-blue-50 hover:border-blue-300 border border-slate-200 rounded-xl text-left transition group cursor-pointer">
                    <div class="flex items-center space-x-1.5 mb-0.5">
                        <span class="w-2 h-2 rounded-full bg-blue-600"></span>
                        <span class="block text-xs font-bold text-slate-900 group-hover:text-blue-700">Super Admin</span>
                    </div>
                    <span class="block text-[10px] text-slate-500 truncate">admin@attention.test</span>
                </button>

                <!-- 2. HR Manager -->
                <button type="button" onclick="fillDemo('hr@attention.test', 'password')" class="p-3 bg-slate-50 hover:bg-blue-50 hover:border-blue-300 border border-slate-200 rounded-xl text-left transition group cursor-pointer">
                    <div class="flex items-center space-x-1.5 mb-0.5">
                        <span class="w-2 h-2 rounded-full bg-indigo-600"></span>
                        <span class="block text-xs font-bold text-slate-900 group-hover:text-indigo-700">HR Manager</span>
                    </div>
                    <span class="block text-[10px] text-slate-500 truncate">hr@attention.test</span>
                </button>

                <!-- 3. IT Supervisor -->
                <button type="button" onclick="fillDemo('supervisor.eng@attention.test', 'password')" class="p-3 bg-slate-50 hover:bg-amber-50 hover:border-amber-300 border border-slate-200 rounded-xl text-left transition group cursor-pointer">
                    <div class="flex items-center space-x-1.5 mb-0.5">
                        <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                        <span class="block text-xs font-bold text-slate-900 group-hover:text-amber-700">IT Supervisor</span>
                    </div>
                    <span class="block text-[10px] text-slate-500 truncate">supervisor.eng@attention.test</span>
                </button>

                <!-- 4. IT Staff -->
                <button type="button" onclick="fillDemo('staff.eng@attention.test', 'password')" class="p-3 bg-slate-50 hover:bg-emerald-50 hover:border-emerald-300 border border-slate-200 rounded-xl text-left transition group cursor-pointer">
                    <div class="flex items-center space-x-1.5 mb-0.5">
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                        <span class="block text-xs font-bold text-slate-900 group-hover:text-emerald-700">IT Staff</span>
                    </div>
                    <span class="block text-[10px] text-slate-500 truncate">staff.eng@attention.test</span>
                </button>
            </div>
        </div>

    </div>

    <script>
        function fillDemo(email, pass) {
            document.getElementById('email').value = email;
            document.getElementById('password').value = pass;
        }
    </script>
</body>
</html>
