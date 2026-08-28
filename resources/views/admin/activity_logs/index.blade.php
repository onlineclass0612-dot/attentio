@extends('layouts.admin', ['title' => 'Log Aktivitas & Audit Trail', 'header' => 'Log Aktivitas Sistem (Audit Trail)'])

@section('content')
<div class="space-y-6" x-data="{
    payloadModal: false,
    payloadData: null,
    payloadTitle: '',
    payloadAction: '',
    activeTab: 'visual',
    copied: false,

    openDiff(title, action, data) {
        this.payloadTitle = title;
        this.payloadAction = action;
        this.payloadData = data;
        this.activeTab = 'visual';
        this.copied = false;
        this.payloadModal = true;
    },

    copyJson() {
        if (this.payloadData) {
            navigator.clipboard.writeText(JSON.stringify(this.payloadData, null, 2));
            this.copied = true;
            setTimeout(() => this.copied = false, 2000);
        }
    },

    isDiff() {
        if (!this.payloadData || typeof this.payloadData !== 'object') return false;
        return ('sebelum' in this.payloadData && 'sesudah' in this.payloadData) ||
               ('old' in this.payloadData && 'new' in this.payloadData);
    },

    getBeforeData() {
        if (!this.payloadData) return {};
        return this.payloadData.sebelum || this.payloadData.old || {};
    },

    getAfterData() {
        if (!this.payloadData) return {};
        return this.payloadData.sesudah || this.payloadData.new || {};
    },

    getDiffKeys() {
        const before = this.getBeforeData();
        const after = this.getAfterData();
        const keys = new Set([...Object.keys(before), ...Object.keys(after)]);
        return Array.from(keys);
    },

    formatLabel(key) {
        return key.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
    },

    formatVal(val) {
        if (val === null || val === undefined) return '-';
        if (typeof val === 'boolean') return val ? 'Ya / Aktif' : 'Tidak / Nonaktif';
        if (typeof val === 'object') return JSON.stringify(val);
        return String(val);
    }
}">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2">
                <h1 class="text-xl font-bold text-slate-900 tracking-tight">Log Aktivitas & Audit Trail</h1>
                <span class="px-2 py-0.5 text-[10px] font-bold bg-blue-100 text-blue-800 rounded-full border border-blue-200/60">Super Admin Only</span>
            </div>
            <p class="text-xs text-slate-500 mt-0.5">Pemantauan transparansi & audit atas pengelolaan master data serta aksi persetujuan sistem</p>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm">
        <form method="GET" action="{{ route('admin.activity_logs.index') }}" class="grid grid-cols-1 sm:grid-cols-5 gap-3">
            <div>
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Cari Keterangan / IP</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari aktivitas..."
                       class="w-full px-3 py-1.5 bg-slate-50 border border-slate-200 rounded-lg text-xs text-slate-900 focus:bg-white focus:ring-2 focus:ring-blue-600">
            </div>

            <div>
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Modul</label>
                <select name="module" class="w-full px-3 py-1.5 bg-slate-50 border border-slate-200 rounded-lg text-xs text-slate-900 focus:bg-white focus:ring-2 focus:ring-blue-600">
                    <option value="">Semua Modul</option>
                    @foreach($modules as $mod)
                        <option value="{{ $mod }}" {{ request('module') == $mod ? 'selected' : '' }}>{{ $mod }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Aksi</label>
                <select name="action" class="w-full px-3 py-1.5 bg-slate-50 border border-slate-200 rounded-lg text-xs text-slate-900 focus:bg-white focus:ring-2 focus:ring-blue-600">
                    <option value="">Semua Aksi</option>
                    @foreach($actions as $act)
                        <option value="{{ $act }}" {{ request('action') == $act ? 'selected' : '' }}>{{ $act }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Tanggal</label>
                <input type="date" name="date" value="{{ request('date') }}"
                       class="w-full px-3 py-1.5 bg-slate-50 border border-slate-200 rounded-lg text-xs text-slate-900 focus:bg-white focus:ring-2 focus:ring-blue-600">
            </div>

            <div class="flex items-end space-x-2">
                <button type="submit" class="flex-1 py-2 px-4 bg-slate-900 hover:bg-slate-800 text-white font-semibold text-xs rounded-lg transition cursor-pointer">
                    Filter Log
                </button>
                @if(request()->anyFilled(['search', 'module', 'action', 'date', 'user_id']))
                    <a href="{{ route('admin.activity_logs.index') }}" class="py-2 px-3 bg-slate-100 hover:bg-slate-200 text-slate-600 font-semibold text-xs rounded-lg transition text-center">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-500 font-semibold border-b border-slate-200/80">
                    <tr>
                        <th class="px-4 py-3.5">Waktu & Tanggal</th>
                        <th class="px-4 py-3.5">Pelaku (*Actor*)</th>
                        <th class="px-4 py-3.5">Modul</th>
                        <th class="px-4 py-3.5">Aksi</th>
                        <th class="px-4 py-3.5">Deskripsi Aktivitas</th>
                        <th class="px-4 py-3.5">IP Address</th>
                        <th class="px-4 py-3.5 text-right">Detail</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($logs as $log)
                        <tr class="hover:bg-slate-50/60 transition">
                            <!-- Waktu -->
                            <td class="px-4 py-3.5 font-mono text-slate-900 whitespace-nowrap">
                                <p class="font-bold text-slate-800">{{ $log->created_at->format('d/m/Y H:i:s') }}</p>
                                <span class="text-[10px] text-slate-400 font-sans">{{ $log->created_at->diffForHumans() }}</span>
                            </td>

                            <!-- Actor -->
                            <td class="px-4 py-3.5 whitespace-nowrap">
                                <div class="flex items-center space-x-2">
                                    <div class="w-7 h-7 rounded-full bg-slate-100 flex items-center justify-center font-bold text-[11px] text-slate-700 ring-1 ring-slate-200">
                                        {{ substr($log->user_name ?? 'U', 0, 1) }}
                                    </div>
                                    <div>
                                        <p class="font-bold text-slate-900">{{ $log->user_name ?? 'System' }}</p>
                                        <span class="inline-block px-1.5 py-0.2 text-[9px] font-bold uppercase rounded bg-slate-100 text-slate-600">
                                            {{ $log->user_role ?? 'User' }}
                                        </span>
                                    </div>
                                </div>
                            </td>

                            <!-- Modul -->
                            <td class="px-4 py-3.5 whitespace-nowrap font-medium text-slate-700">
                                <span class="px-2 py-0.5 text-[10px] font-semibold bg-slate-100 border border-slate-200/80 rounded-md text-slate-800">
                                    {{ $log->module }}
                                </span>
                            </td>

                            <!-- Aksi Badge -->
                            <td class="px-4 py-3.5 whitespace-nowrap">
                                @php
                                    $actionClass = match(strtoupper($log->action)) {
                                        'CREATE', 'APPROVE' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                                        'UPDATE' => 'bg-amber-100 text-amber-800 border-amber-200',
                                        'DELETE', 'REJECT' => 'bg-rose-100 text-rose-800 border-rose-200',
                                        default => 'bg-slate-100 text-slate-700 border-slate-200',
                                    };
                                @endphp
                                <span class="px-2 py-0.5 text-[10px] font-bold uppercase rounded border {{ $actionClass }}">
                                    {{ $log->action }}
                                </span>
                            </td>

                            <!-- Deskripsi -->
                            <td class="px-4 py-3.5 text-slate-800 font-medium max-w-xs sm:max-w-sm">
                                {{ $log->description }}
                            </td>

                            <!-- IP Address -->
                            <td class="px-4 py-3.5 font-mono text-[11px] text-slate-500 whitespace-nowrap">
                                {{ $log->ip_address ?? '127.0.0.1' }}
                            </td>

                            <!-- Action Detail JSON / Visual Diff -->
                            <td class="px-4 py-3.5 text-right whitespace-nowrap">
                                @if(!empty($log->properties))
                                    <button type="button" 
                                            @click="openDiff('{{ addslashes($log->description) }}', '{{ $log->action }}', {{ json_encode($log->properties) }})"
                                            class="px-2.5 py-1.5 bg-blue-50 hover:bg-blue-100 text-blue-700 font-semibold text-[11px] rounded-lg transition border border-blue-200 flex items-center space-x-1.5 ml-auto cursor-pointer">
                                        <svg class="w-3.5 h-3.5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                                        <span>Lihat Rincian</span>
                                    </button>
                                @else
                                    <span class="text-slate-400 text-[11px]">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="p-8 text-center text-xs text-slate-400">
                                Belum ada catatan aktivitas sistem yang tercatat pada filter ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($logs->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $logs->links() }}
            </div>
        @endif
    </div>

    <!-- Interactive Visual Diff Modal -->
    <div x-show="payloadModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm" style="display: none;">
        <div class="bg-white rounded-2xl max-w-2xl w-full shadow-2xl border border-slate-200 overflow-hidden text-left flex flex-col max-h-[85vh]" @click.away="payloadModal = false">
            
            <!-- Modal Header -->
            <div class="p-5 border-b border-slate-200 bg-slate-50/50 flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="w-9 h-9 rounded-xl bg-blue-100 text-blue-700 flex items-center justify-center font-bold">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-slate-900">Rincian Data Aktivitas</h3>
                        <p class="text-xs text-slate-500 truncate max-w-md" x-text="payloadTitle"></p>
                    </div>
                </div>

                <!-- Tabs: Visual vs Raw JSON -->
                <div class="flex items-center bg-slate-200/70 p-1 rounded-xl space-x-1">
                    <button type="button" 
                            @click="activeTab = 'visual'"
                            :class="activeTab === 'visual' ? 'bg-white text-slate-900 shadow-xs font-bold' : 'text-slate-600 hover:text-slate-900 font-medium'"
                            class="px-3 py-1 text-xs rounded-lg transition cursor-pointer">
                        📊 Tampilan Tabel
                    </button>
                    <button type="button" 
                            @click="activeTab = 'json'"
                            :class="activeTab === 'json' ? 'bg-white text-slate-900 shadow-xs font-bold' : 'text-slate-600 hover:text-slate-900 font-medium'"
                            class="px-3 py-1 text-xs rounded-lg transition cursor-pointer">
                        ⚙️ Raw JSON
                    </button>
                </div>
            </div>

            <!-- Modal Body (Scrollable) -->
            <div class="p-5 overflow-y-auto flex-1 space-y-4">
                
                <!-- TAB 1: VISUAL VIEW -->
                <div x-show="activeTab === 'visual'" class="space-y-4">
                    
                    <!-- Case 1: Approval APPROVE (Hanya tampilkan Deskripsi Persetujuan) -->
                    <template x-if="payloadAction === 'APPROVE'">
                        <div class="p-5 rounded-2xl bg-emerald-50/80 border border-emerald-200/80 shadow-xs space-y-3">
                            <div class="flex items-center space-x-2.5 text-emerald-800 font-bold text-sm">
                                <div class="w-7 h-7 rounded-full bg-emerald-100 flex items-center justify-center text-emerald-700">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                                </div>
                                <span>Status: Disetujui (Approved)</span>
                            </div>
                            <div class="bg-white p-4 rounded-xl border border-emerald-100 text-xs text-slate-800 leading-relaxed font-medium">
                                <p class="text-[10px] font-bold text-emerald-700 uppercase tracking-wider mb-1">Keterangan Persetujuan:</p>
                                <p x-text="payloadData?.deskripsi || payloadTitle"></p>
                            </div>
                        </div>
                    </template>

                    <!-- Case 2: Approval REJECT (Hanya tampilkan Alasan Penolakan) -->
                    <template x-if="payloadAction === 'REJECT'">
                        <div class="p-5 rounded-2xl bg-rose-50/80 border border-rose-200/80 shadow-xs space-y-3">
                            <div class="flex items-center space-x-2.5 text-rose-800 font-bold text-sm">
                                <div class="w-7 h-7 rounded-full bg-rose-100 flex items-center justify-center text-rose-700">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
                                </div>
                                <span>Status: Permohonan Ditolak (Rejected)</span>
                            </div>
                            <div class="bg-white p-4 rounded-xl border border-rose-100 text-xs text-slate-800 leading-relaxed font-medium">
                                <p class="text-[10px] font-bold text-rose-700 uppercase tracking-wider mb-1">Alasan Penolakan (Rejection Reason):</p>
                                <p class="text-rose-950 font-semibold" x-text="payloadData?.alasan_penolakan || payloadData?.reason || 'Tidak ada alasan khusus yang dicantumkan.'"></p>
                            </div>
                        </div>
                    </template>

                    <!-- Case 3: Perubahan Master Data UPDATE (Sebelum vs Sesudah) -->
                    <template x-if="payloadAction !== 'APPROVE' && payloadAction !== 'REJECT' && isDiff()">
                        <div class="border border-slate-200 rounded-xl overflow-hidden shadow-xs">
                            <table class="w-full text-left text-xs">
                                <thead class="bg-slate-50 text-slate-600 font-bold border-b border-slate-200">
                                    <tr>
                                        <th class="px-4 py-2.5 w-1/3">Kolom / Atribut</th>
                                        <th class="px-4 py-2.5 w-1/3 bg-rose-50/60 text-rose-900 border-l border-r border-rose-100">Sebelum (Lama)</th>
                                        <th class="px-4 py-2.5 w-1/3 bg-emerald-50/60 text-emerald-900">Sesudah (Baru)</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    <template x-for="key in getDiffKeys()" :key="key">
                                        <tr class="hover:bg-slate-50/50">
                                            <td class="px-4 py-2.5 font-bold text-slate-700" x-text="formatLabel(key)"></td>
                                            <td class="px-4 py-2.5 bg-rose-50/20 text-rose-800 border-l border-r border-rose-100 font-medium"
                                                :class="formatVal(getBeforeData()[key]) !== formatVal(getAfterData()[key]) ? 'line-through text-rose-600' : ''"
                                                x-text="formatVal(getBeforeData()[key])">
                                            </td>
                                            <td class="px-4 py-2.5 bg-emerald-50/20 font-bold"
                                                :class="formatVal(getBeforeData()[key]) !== formatVal(getAfterData()[key]) ? 'text-emerald-700 bg-emerald-100/40' : 'text-slate-800'"
                                                x-text="formatVal(getAfterData()[key])">
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </template>

                    <!-- Case 4: Data Baru / Snapshot Biasa (Single Object) -->
                    <template x-if="payloadAction !== 'APPROVE' && payloadAction !== 'REJECT' && !isDiff() && payloadData">
                        <div class="border border-slate-200 rounded-xl overflow-hidden shadow-xs">
                            <table class="w-full text-left text-xs">
                                <thead class="bg-slate-50 text-slate-600 font-bold border-b border-slate-200">
                                    <tr>
                                        <th class="px-4 py-2.5 w-1/3">Field / Parameter</th>
                                        <th class="px-4 py-2.5 w-2/3">Nilai Data</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    <template x-for="[key, val] in Object.entries(payloadData)" :key="key">
                                        <tr class="hover:bg-slate-50/50">
                                            <td class="px-4 py-2.5 font-bold text-slate-700" x-text="formatLabel(key)"></td>
                                            <td class="px-4 py-2.5 text-slate-900 font-medium font-mono" x-text="formatVal(val)"></td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </template>
                </div>

                <!-- TAB 2: RAW JSON VIEW -->
                <div x-show="activeTab === 'json'" class="relative">
                    <div class="bg-slate-900 rounded-xl p-4 overflow-x-auto max-h-80 border border-slate-800 shadow-inner">
                        <pre class="text-emerald-400 font-mono text-xs" x-text="JSON.stringify(payloadData, null, 2)"></pre>
                    </div>

                    <button type="button" 
                            @click="copyJson()" 
                            class="absolute top-3 right-3 px-3 py-1.5 bg-slate-800 hover:bg-slate-700 text-slate-200 font-semibold text-[11px] rounded-lg transition border border-slate-700 shadow-xs flex items-center space-x-1 cursor-pointer">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                        <span x-text="copied ? 'Tersalin!' : 'Salin JSON'"></span>
                    </button>
                </div>

            </div>

            <!-- Modal Footer -->
            <div class="p-4 border-t border-slate-200 bg-slate-50 flex items-center justify-between">
                <span class="text-[11px] text-slate-400">Data terenkripsi dan disimpan secara permanen untuk audit.</span>
                <button type="button" @click="payloadModal = false" class="px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white font-semibold text-xs rounded-xl transition cursor-pointer">
                    Tutup
                </button>
            </div>

        </div>
    </div>

</div>
@endsection
