@props(['score'])

@php
    $value = $score !== null ? (float) $score : null;
    $colorClass = match (true) {
        $value === null => 'bg-slate-800 text-slate-400',
        $value >= 70 => 'bg-emerald-400/20 text-emerald-400',
        $value >= 40 => 'bg-amber-400/20 text-amber-400',
        default => 'bg-rose-500/20 text-rose-400',
    };
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center rounded-lg px-2 py-1 text-xs font-semibold {$colorClass}"]) }}>
    {{ $value !== null ? number_format($value, 0) : '—' }}
</span>
