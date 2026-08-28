@extends('layouts.admin', ['title' => 'Tambah Kantor Baru', 'header' => 'Tambah Kantor & Geofence'])

@section('content')
<div class="max-w-3xl mx-auto space-y-6" x-data="branchPicker()">

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-slate-900 tracking-tight">Tambah Lokasi Kantor</h1>
            <p class="text-xs text-slate-500 mt-0.5">Klik pada peta untuk memilih titik koordinat kantor</p>
        </div>
        <a href="{{ route('admin.branches.index') }}" class="text-xs font-semibold text-slate-600 hover:text-slate-900">
            &larr; Kembali
        </a>
    </div>

    <form action="{{ route('admin.branches.store') }}" method="POST" class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-sm space-y-5">
        @csrf

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Kantor / Cabang</label>
                <input type="text" name="name" value="{{ old('name') }}" required placeholder="Contoh: Kantor Cabang Surabaya"
                       class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-lg text-xs text-slate-900 focus:bg-white focus:ring-2 focus:ring-blue-600">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Kode Cabang</label>
                <input type="text" name="code" value="{{ old('code') }}" required placeholder="Contoh: BR-SBY"
                       class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-lg text-xs text-slate-900 focus:bg-white focus:ring-2 focus:ring-blue-600">
            </div>
        </div>

        <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1">Alamat Lengkap</label>
            <textarea name="address" rows="2" placeholder="Tuliskan alamat fisik kantor..."
                      class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-lg text-xs text-slate-900 focus:bg-white focus:ring-2 focus:ring-blue-600">{{ old('address') }}</textarea>
        </div>

        <!-- Interactive Leaflet Map Picker -->
        <div>
            <label class="block text-xs font-bold text-slate-800 mb-2">Pilih Titik Lokasi pada Peta:</label>
            <div id="pickerMap" class="w-full h-64 rounded-xl border border-slate-200 z-10"></div>
            <p class="text-[11px] text-slate-400 mt-1">Klik di mana saja pada peta untuk memperbarui titik koordinat dan radius geofence.</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Latitude</label>
                <input type="number" step="any" name="latitude" x-model="lat" required
                       class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-lg text-xs text-slate-900 font-mono focus:bg-white focus:ring-2 focus:ring-blue-600">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Longitude</label>
                <input type="number" step="any" name="longitude" x-model="lng" required
                       class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-lg text-xs text-slate-900 font-mono focus:bg-white focus:ring-2 focus:ring-blue-600">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Radius Toleransi (Meter)</label>
                <input type="number" name="radius_meters" x-model="radius" @input="updateCircle()" required min="10" max="5000"
                       class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-lg text-xs text-slate-900 font-mono focus:bg-white focus:ring-2 focus:ring-blue-600">
            </div>
        </div>

        <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1">Status Lokasi</label>
            <select name="is_active" required class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-lg text-xs text-slate-900 focus:bg-white focus:ring-2 focus:ring-blue-600">
                <option value="1">Aktif</option>
                <option value="0">Non-Aktif</option>
            </select>
        </div>

        <div class="pt-3 border-t border-slate-100 flex items-center justify-end space-x-2">
            <a href="{{ route('admin.branches.index') }}" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-lg transition">
                Batal
            </a>
            <button type="submit" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs rounded-xl transition shadow-md shadow-blue-600/30">
                Simpan Lokasi
            </button>
        </div>
    </form>

</div>

@push('scripts')
<script>
function branchPicker() {
    return {
        lat: -6.225588,
        lng: 106.809180,
        radius: 100,
        map: null,
        marker: null,
        circle: null,

        init() {
            this.$nextTick(() => {
                this.map = L.map('pickerMap').setView([this.lat, this.lng], 15);

                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '&copy; OpenStreetMap'
                }).addTo(this.map);

                this.marker = L.marker([this.lat, this.lng], { draggable: true }).addTo(this.map);
                this.circle = L.circle([this.lat, this.lng], {
                    color: '#2563eb',
                    fillColor: '#3b82f6',
                    fillOpacity: 0.2,
                    radius: this.radius
                }).addTo(this.map);

                this.map.on('click', (e) => {
                    this.lat = e.latlng.lat.toFixed(8);
                    this.lng = e.latlng.lng.toFixed(8);
                    this.updateMapPosition();
                });

                this.marker.on('dragend', (e) => {
                    const pos = e.target.getLatLng();
                    this.lat = pos.lat.toFixed(8);
                    this.lng = pos.lng.toFixed(8);
                    this.updateMapPosition();
                });
            });
        },

        updateMapPosition() {
            const pos = [this.lat, this.lng];
            this.marker.setLatLng(pos);
            this.circle.setLatLng(pos);
            this.circle.setRadius(this.radius);
            this.map.panTo(pos);
        },

        updateCircle() {
            if (this.circle) {
                this.circle.setRadius(this.radius || 100);
            }
        }
    }
}
</script>
@endpush
@endsection
