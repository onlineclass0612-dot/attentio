@props([
    'type' => 'info',
    'variant' => 'soft',
    'title' => null,
    'dismissible' => true,
])

<x-radix-alert :type="$type" :variant="$variant" :title="$title" :dismissible="$dismissible" {{ $attributes }}>
    {{ $slot }}
</x-radix-alert>
