<div x-data="{
    isOpen: false,
    title: 'Konfirmasi Tindakan',
    description: 'Apakah Anda yakin ingin melanjutkan tindakan ini? Tindakan ini tidak dapat dibatalkan.',
    confirmText: 'Ya, Hapus',
    cancelText: 'Batal',
    type: 'destructive',
    targetForm: null,
    onConfirm: null,
    
    open(options = {}) {
        this.title = options.title || 'Konfirmasi Tindakan';
        this.description = options.description || 'Apakah Anda yakin ingin melanjutkan?';
        this.confirmText = options.confirmText || (options.type === 'info' ? 'Lanjutkan' : 'Ya, Hapus');
        this.cancelText = options.cancelText || 'Batal';
        this.type = options.type || 'destructive';
        this.targetForm = options.form || null;
        this.onConfirm = typeof options.onConfirm === 'function' ? options.onConfirm : null;
        this.isOpen = true;
    },
    
    close() {
        this.isOpen = false;
    },
    
    confirm() {
        if (this.targetForm) {
            this.targetForm.submit();
        } else if (this.onConfirm) {
            this.onConfirm();
        }
        this.close();
    }
}"
@open-alert-dialog.window="open($event.detail)"
@keydown.escape.window="if (isOpen) close()"
class="relative z-50">

    <!-- Radix AlertDialog.Overlay -->
    <div x-show="isOpen"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs z-50"
         aria-hidden="true"
         style="display: none;"></div>

    <!-- Radix AlertDialog.Content -->
    <div x-show="isOpen"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95 translate-y-2"
         x-transition:enter-end="opacity-100 scale-100 translate-y-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 scale-100 translate-y-0"
         x-transition:leave-end="opacity-0 scale-95 translate-y-2"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 overflow-y-auto"
         role="alertdialog"
         aria-modal="true"
         aria-labelledby="alert-dialog-title"
         aria-describedby="alert-dialog-description"
         style="display: none;">
         
        <div class="relative w-full max-w-md bg-white rounded-2xl border border-slate-200/90 shadow-2xl p-6 space-y-4 text-left"
             @click.away="close()">
            
            <!-- AlertDialog.Header -->
            <div class="flex items-start gap-4">
                <!-- Icon based on type -->
                <div class="flex-shrink-0 p-2.5 rounded-xl mt-0.5"
                     :class="{
                        'bg-rose-50 text-rose-600 border border-rose-200/80': type === 'destructive',
                        'bg-amber-50 text-amber-600 border border-amber-200/80': type === 'warning',
                        'bg-blue-50 text-blue-600 border border-blue-200/80': type === 'info'
                     }">
                    <template x-if="type === 'destructive'">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/>
                        </svg>
                    </template>
                    <template x-if="type === 'warning'">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 18h.01"/>
                        </svg>
                    </template>
                    <template x-if="type === 'info'">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z"/>
                        </svg>
                    </template>
                </div>

                <div class="flex-1 pt-0.5">
                    <h3 id="alert-dialog-title" class="text-base font-bold text-slate-900 tracking-tight" x-text="title"></h3>
                    <p id="alert-dialog-description" class="text-xs text-slate-500 mt-1.5 leading-relaxed" x-text="description"></p>
                </div>
            </div>

            <!-- AlertDialog.Footer -->
            <div class="pt-3 border-t border-slate-100 flex items-center justify-end space-x-2.5">
                <!-- AlertDialog.Cancel -->
                <button type="button" 
                        @click="close()" 
                        class="px-4 py-2 bg-white hover:bg-slate-100 text-slate-700 font-semibold text-xs rounded-xl border border-slate-200 shadow-xs transition-colors cursor-pointer"
                        x-text="cancelText">
                </button>

                <!-- AlertDialog.Action -->
                <button type="button" 
                        @click="confirm()" 
                        class="px-4 py-2 font-bold text-xs rounded-xl transition-all shadow-md cursor-pointer flex items-center space-x-1.5"
                        :class="{
                            'bg-rose-600 hover:bg-rose-700 text-white shadow-rose-600/30': type === 'destructive',
                            'bg-amber-600 hover:bg-amber-700 text-white shadow-amber-600/30': type === 'warning',
                            'bg-blue-600 hover:bg-blue-700 text-white shadow-blue-600/30': type === 'info'
                        }">
                    <span x-text="confirmText"></span>
                </button>
            </div>

        </div>
    </div>

</div>
