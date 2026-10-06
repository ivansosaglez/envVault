{{-- One side of a comparison row. Secrets stay masked: comparison never reveals them. --}}
@props(['variable' => null, 'absent' => '—', 'absentTone' => 'muted'])

@if ($variable === null)
    <span @class(['text-xs', 'font-medium text-red-600 dark:text-red-400' => $absentTone === 'danger', 'text-zinc-400' => $absentTone !== 'danger'])>{{ $absent }}</span>
@elseif ($variable->is_secret)
    <span class="inline-flex items-center gap-2">
        <span class="ev-mono select-none tracking-wider text-zinc-400">{{ $variable->hasValue() ? \App\Models\EnvironmentVariable::MASK : '' }}</span>
        @unless ($variable->hasValue()) <span class="text-xs italic text-zinc-400">empty</span> @endunless
    </span>
@elseif ($variable->hasValue())
    <span class="ev-mono block max-w-[18rem] truncate" title="{{ $variable->value }}">{{ $variable->value }}</span>
@else
    <span class="text-xs italic text-zinc-400">empty</span>
@endif
