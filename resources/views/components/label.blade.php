@props(['value' => null, 'required' => false])

<label class="text-xs font-bold text-base-content uppercase tracking-widest">{{ $value ?? $slot }} @if($required)<span class="text-red-500">*</span>@endif</label>
