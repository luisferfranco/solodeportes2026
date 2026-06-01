@props(['value' => null])

<label class="text-xs font-bold text-base-content uppercase tracking-widest">{{ $value ?? $slot }}</label>
