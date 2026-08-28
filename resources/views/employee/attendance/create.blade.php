@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto space-y-5" x-data="attendanceHandler()">

    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-slate-900 tracking-tight">Presensi Kehadiran</h1>
            <p class="text-xs text-slate-500 mt-0.5">Lokasi: <strong>{{ $branch->name ?? 'Kantor Pusat' }}</strong> (Radius: {{ $branch->radius_meters ?? 100 }}m)</p>
        </div>
        <a href="{{ route('employee.dashboard') }}" class="text-xs font-semibold text-slate-500 hover:text-slate-900">
            &larr; Kembali
        </a>
    </div>

    <!-- Alert / Feedback Banner (Radix UI Callout) -->
    <div x-show="alertMessage" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 -translate-y-1 scale-98"
         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 -translate-y-1 scale-98"
         :class="alertType === 'success' 
            ? 'bg-emerald-500/[0.08] border-emerald-500/25 text-emerald-950' 
            : 'bg-rose-500/[0.08] border-rose-500/25 text-rose-950'"
         class="p-4 rounded-xl border backdrop-blur-xs text-xs font-semibold flex items-start gap-3.5 sm:gap-4 shadow-xs" 
         style="display: none;">
        <div :class="alertType === 'success' ? 'bg-emerald-500/15 text-emerald-600' : 'bg-rose-500/15 text-rose-600'" 
             class="flex-shrink-0 mt-0.5 p-1.5 rounded-lg">
            <template x-if="alertType === 'success'">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.25" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </template>
            <template x-if="alertType !== 'success'">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.25" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 7.5h.01"/>
                </svg>
            </template>
        </div>
        <div class="flex-1 min-w-0 pt-0.5">
            <span x-text="alertMessage" class="leading-relaxed"></span>
        </div>
        <button type="button" @click="alertMessage = ''" class="flex-shrink-0 text-slate-400 hover:text-slate-600 p-1 rounded-md">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
    </div>

    <!-- 1. Live Camera Snapshot Card -->
    <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-sm space-y-3">
        <div class="flex items-center justify-between">
            <span class="text-xs font-bold text-slate-800 flex items-center">
                <svg class="w-4 h-4 text-blue-600 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                Verifikasi Kamera / Swafoto
            </span>
            <span class="text-[11px] text-slate-400">Wajah harus terlihat jelas</span>
        </div>

        <div class="relative w-full aspect-video bg-slate-900 rounded-xl overflow-hidden flex items-center justify-center border border-slate-200">
            <!-- Video Stream -->
            <video id="webcamVideo" autoplay playsinline class="w-full h-full object-cover mirror" x-show="!photoCaptured"></video>
            
            <!-- Snapshot Preview -->
            <img :src="photoData" class="w-full h-full object-cover" x-show="photoCaptured" style="display: none;">

            <!-- Capture Button Overlay -->
            <div class="absolute bottom-3 inset-x-0 flex justify-center space-x-2">
                <button type="button" 
                        @click="takeSnapshot()" 
                        x-show="!photoCaptured"
                        class="px-4 py-2 bg-white/90 hover:bg-white text-slate-900 font-bold text-xs rounded-full shadow-lg backdrop-blur-sm transition flex items-center space-x-1.5">
                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path></svg>
                    <span>Ambil Foto</span>
                </button>

                <button type="button" 
                        @click="retakePhoto()" 
                        x-show="photoCaptured"
                        class="px-4 py-2 bg-slate-900/80 hover:bg-slate-900 text-white font-bold text-xs rounded-full shadow-lg backdrop-blur-sm transition flex items-center space-x-1.5"
                        style="display: none;">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                    <span>Foto Ulang</span>
                </button>
            </div>
            
            <canvas id="photoCanvas" class="hidden"></canvas>
        </div>
    </div>

    <!-- 2. Geolocation & Interactive Leaflet Map Card -->
    <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-sm space-y-3">
        <div class="flex items-center justify-between">
            <span class="text-xs font-bold text-slate-800 flex items-center">
                <svg class="w-4 h-4 text-blue-600 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                Validasi Lokasi (GPS Geofencing)
            </span>
            
            <!-- Distance & Geofence Badge -->
            <span class="px-2.5 py-1 text-xs font-bold rounded-full transition"
                  :class="isInsideGeofence ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800'">
                <span x-text="geofenceStatusText">Mendeteksi GPS...</span>
            </span>
        </div>

        <!-- Leaflet Map Container -->
        <div id="attendanceMap" class="w-full h-48 rounded-xl border border-slate-200 z-10"></div>
        
        <p class="text-[11px] text-slate-500">
            Titik Biru: Lokasi Anda &bull; Titik Merah: Lokasi Kantor &bull; Lingkaran: Radius Toleransi Absensi ({{ $branch->radius_meters ?? 100 }}m).
        </p>
    </div>

    <!-- 3. Notes / Catatan Opsional -->
    <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-sm space-y-2">
        <label for="notes" class="text-xs font-bold text-slate-800 block">Catatan Kehadiran (Opsional):</label>
        <input type="text" x-model="notes" id="notes" 
               class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-lg text-xs text-slate-900 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-600"
               placeholder="Contoh: Bekerja dari kantor cabang / meeting eksternal">
    </div>

    <!-- 4. Action Buttons (Clock In / Clock Out) -->
    <div class="space-y-3 pt-2">
        @if(!$attendanceToday || !$attendanceToday->clock_in)
            <button type="button" 
                    @click="submitAttendance('in')" 
                    :disabled="loading || !isInsideGeofence"
                    class="w-full py-4 px-6 bg-blue-600 hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed text-white font-bold rounded-xl shadow-lg shadow-blue-600/30 transition flex items-center justify-center space-x-2 text-base">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path></svg>
                <span x-text="loading ? 'Memproses Presensi...' : 'Clock In Sekarang (Masuk)'"></span>
            </button>
        @elseif(!$attendanceToday->clock_out)
            <button type="button" 
                    @click="submitAttendance('out')" 
                    :disabled="loading || !isInsideGeofence"
                    class="w-full py-4 px-6 bg-rose-600 hover:bg-rose-700 disabled:opacity-50 disabled:cursor-not-allowed text-white font-bold rounded-xl shadow-lg shadow-rose-600/30 transition flex items-center justify-center space-x-2 text-base">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                <span x-text="loading ? 'Memproses Presensi...' : 'Clock Out Sekarang (Pulang)'"></span>
            </button>
        @else
            <div class="p-4 bg-emerald-50 rounded-xl border border-emerald-200 text-center">
                <p class="text-sm font-bold text-emerald-800">Presensi Hari Ini Lengkap!</p>
                <p class="text-xs text-emerald-600 mt-1">
                    Masuk: {{ Carbon\Carbon::parse($attendanceToday->clock_in)->format('H:i') }} WIB &bull; 
                    Pulang: {{ Carbon\Carbon::parse($attendanceToday->clock_out)->format('H:i') }} WIB
                </p>
            </div>
        @endif
    </div>

</div>

@push('scripts')
<script>
function attendanceHandler() {
    return {
        branchLat: {{ $branch->latitude ?? -6.225588 }},
        branchLng: {{ $branch->longitude ?? 106.809180 }},
        branchRadius: {{ $branch->radius_meters ?? 150 }},
        userLat: null,
        userLng: null,
        distanceMeters: null,
        isInsideGeofence: false,
        geofenceStatusText: 'Mendeteksi lokasi...',
        
        photoData: null,
        photoCaptured: false,
        notes: '',
        loading: false,
        alertMessage: '',
        alertType: '',
        map: null,
        userMarker: null,

        init() {
            this.initCamera();
            this.initGeolocation();
        },

        initCamera() {
            const video = document.getElementById('webcamVideo');
            if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
                navigator.mediaDevices.getUserMedia({ video: { facingMode: 'user' } })
                    .then(stream => {
                        video.srcObject = stream;
                    })
                    .catch(err => {
                        console.warn("Kamera tidak dapat diakses:", err);
                    });
            }
        },

        takeSnapshot() {
            const video = document.getElementById('webcamVideo');
            const canvas = document.getElementById('photoCanvas');
            canvas.width = video.videoWidth || 640;
            canvas.height = video.videoHeight || 480;
            const ctx = canvas.getContext('2d');
            ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
            this.photoData = canvas.toDataURL('image/jpeg', 0.8);
            this.photoCaptured = true;
        },

        retakePhoto() {
            this.photoData = null;
            this.photoCaptured = false;
        },

        initGeolocation() {
            if (!navigator.geolocation) {
                this.geofenceStatusText = 'GPS tidak didukung browser';
                return;
            }

            navigator.geolocation.getCurrentPosition(
                (pos) => {
                    this.userLat = pos.coords.latitude;
                    this.userLng = pos.coords.longitude;
                    this.calculateDistance();
                    this.renderMap();
                },
                (err) => {
                    // Fallback to branch coordinate if permission denied (for demo convenience)
                    console.warn("GPS error:", err);
                    this.userLat = this.branchLat;
                    this.userLng = this.branchLng;
                    this.calculateDistance();
                    this.renderMap();
                },
                { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
            );
        },

        calculateDistance() {
            const R = 6371000; // Radius bumi dalam meter
            const dLat = (this.branchLat - this.userLat) * Math.PI / 180;
            const dLon = (this.branchLng - this.userLng) * Math.PI / 180;
            const a = Math.sin(dLat/2) * Math.sin(dLat/2) +
                      Math.cos(this.userLat * Math.PI / 180) * Math.cos(this.branchLat * Math.PI / 180) *
                      Math.sin(dLon/2) * Math.sin(dLon/2);
            const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
            this.distanceMeters = Math.round(R * c);

            this.isInsideGeofence = this.distanceMeters <= this.branchRadius;
            if (this.isInsideGeofence) {
                this.geofenceStatusText = `Di Dalam Radius (${this.distanceMeters}m)`;
            } else {
                this.geofenceStatusText = `Di Luar Radius (${this.distanceMeters}m)`;
            }
        },

        renderMap() {
            if (this.map) return;

            this.map = L.map('attendanceMap').setView([this.userLat, this.userLng], 16);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap'
            }).addTo(this.map);

            // Office Circle Radius
            L.circle([this.branchLat, this.branchLng], {
                color: '#2563eb',
                fillColor: '#3b82f6',
                fillOpacity: 0.15,
                radius: this.branchRadius
            }).addTo(this.map);

            // Office Marker
            L.marker([this.branchLat, this.branchLng]).addTo(this.map)
                .bindPopup('<b>{{ $branch->name ?? "Kantor Pusat" }}</b><br>Radius: {{ $branch->radius_meters ?? 100 }}m');

            // User Marker
            this.userMarker = L.circleMarker([this.userLat, this.userLng], {
                radius: 8,
                color: '#ffffff',
                fillColor: '#2563eb',
                fillOpacity: 1,
                weight: 2
            }).addTo(this.map).bindPopup('Lokasi Anda Saat Ini').openPopup();
        },

        async submitAttendance(type) {
            if (!this.photoCaptured) {
                this.takeSnapshot();
            }

            this.loading = true;
            this.alertMessage = '';

            const url = type === 'in' 
                ? "{{ route('employee.attendance.clock-in') }}" 
                : "{{ route('employee.attendance.clock-out') }}";

            try {
                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        latitude: this.userLat,
                        longitude: this.userLng,
                        photo: this.photoData,
                        notes: this.notes
                    })
                });

                const data = await response.json();

                if (response.ok && data.success) {
                    this.alertType = 'success';
                    this.alertMessage = data.message;
                    setTimeout(() => {
                        window.location.href = "{{ route('employee.dashboard') }}";
                    }, 1500);
                } else {
                    this.alertType = 'error';
                    this.alertMessage = data.message || 'Terjadi kendala saat menyimpan presensi.';
                }
            } catch (err) {
                this.alertType = 'error';
                this.alertMessage = 'Terjadi kesalahan koneksi ke server.';
            } finally {
                this.loading = false;
            }
        }
    }
}
</script>
<style>
.mirror {
    transform: scaleX(-1);
}
</style>
@endpush
@endsection
