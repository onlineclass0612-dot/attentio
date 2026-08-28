@props([
    'type' => 'info', // 'info', 'success', 'warning', 'error', 'destructive'
    'variant' => 'soft', // 'soft', 'surface', 'outline'
    'title' => null,
    'dismissible' => true,
])

@php
    $normalizedType = match ($type) {
        'success' => 'success',
        'warning' => 'warning',
        'error', 'destructive', 'danger' => 'destructive',
        default => 'info',
    };

    $typeStyles = [
        'info' => [
            'root' => 'bg-blue-500/[0.08] border-blue-500/25 text-blue-950 dark:bg-blue-950/40 dark:border-blue-500/30 dark:text-blue-100',
            'iconBg' => 'bg-blue-500/15 text-blue-600 dark:text-blue-400',
            'title' => 'text-blue-900 dark:text-blue-200',
            'body' => 'text-blue-800/90 dark:text-blue-300/90',
            'close' => 'text-blue-700/60 hover:text-blue-900 hover:bg-blue-500/15',
        ],
        'success' => [
            'root' => 'bg-emerald-500/[0.08] border-emerald-500/25 text-emerald-950 dark:bg-emerald-950/40 dark:border-emerald-500/30 dark:text-emerald-100',
            'iconBg' => 'bg-emerald-500/15 text-emerald-600 dark:text-emerald-400',
            'title' => 'text-emerald-900 dark:text-emerald-200',
            'body' => 'text-emerald-800/90 dark:text-emerald-300/90',
            'close' => 'text-emerald-700/60 hover:text-emerald-900 hover:bg-emerald-500/15',
        ],
        'warning' => [
            'root' => 'bg-amber-500/[0.08] border-amber-500/25 text-amber-950 dark:bg-amber-950/40 dark:border-amber-500/30 dark:text-amber-100',
            'iconBg' => 'bg-amber-500/15 text-amber-600 dark:text-amber-400',
            'title' => 'text-amber-900 dark:text-amber-200',
            'body' => 'text-amber-800/90 dark:text-amber-300/90',
            'close' => 'text-amber-700/60 hover:text-amber-900 hover:bg-amber-500/15',
        ],
        'destructive' => [
            'root' => 'bg-rose-500/[0.08] border-rose-500/25 text-rose-950 dark:bg-rose-950/40 dark:border-rose-500/30 dark:text-rose-100',
            'iconBg' => 'bg-rose-500/15 text-rose-600 dark:text-rose-400',
            'title' => 'text-rose-900 dark:text-rose-200',
            'body' => 'text-rose-800/90 dark:text-rose-300/90',
            'close' => 'text-rose-700/60 hover:text-rose-900 hover:bg-rose-500/15',
        ],
    ][$normalizedType];
@endphp

<div x-data="{ show: true }" 
     x-show="show"
     x-transition:enter="transition ease-out duration-200"
     x-transition:enter-start="opacity-0 -translate-y-1 scale-98"
     x-transition:enter-end="opacity-100 translate-y-0 scale-100"
     x-transition:leave="transition ease-in duration-150"
     x-transition:leave-start="opacity-100 scale-100"
     x-transition:leave-end="opacity-0 -translate-y-1 scale-98"
     role="alert"
     {{ $attributes->merge(['class' => 'relative flex items-start gap-3.5 sm:gap-4 p-4 rounded-xl border backdrop-blur-xs shadow-xs text-xs transition-all ' . $typeStyles['root']]) }}>
    
    <!-- Radix Callout.Icon -->
    <div class="flex-shrink-0 mt-0.5 p-1.5 rounded-lg {{ $typeStyles['iconBg'] }}">
        @if (isset($icon))
            {{ $icon }}
        @elseif ($normalizedType === 'success')
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.25" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        @elseif ($normalizedType === 'warning')
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.25" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 18h.01"/>
            </svg>
        @elseif ($normalizedType === 'destructive')
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.25" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 7.5h.01"/>
            </svg>
        @else
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.25" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z"/>
            </svg>
        @endif
    </div>

    <!-- Radix Callout.Text -->
    <div class="flex-1 min-w-0 pr-2 pt-0.5">
        @if ($title)
            <h5 class="font-bold text-xs leading-none tracking-tight mb-1.5 {{ $typeStyles['title'] }}">
                {{ $title }}
            </h5>
        @endif
        <div class="leading-relaxed font-medium {{ $typeStyles['body'] }}">
            {{ $slot }}
        </div>
    </div>

    <!-- Radix Callout Close Trigger -->
    @if ($dismissible)
        <button type="button" 
                @click="show = false" 
                class="flex-shrink-0 -mr-1 -mt-1 p-1 rounded-md transition-colors cursor-pointer {{ $typeStyles['close'] }}"
                aria-label="Tutup notifikasi">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    @endif
</div>
