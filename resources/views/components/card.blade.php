@props(['padding' => true])

<div {{ $attributes->class(['ev-card', 'p-5 sm:p-6' => $padding]) }}>
    {{ $slot }}
</div>
