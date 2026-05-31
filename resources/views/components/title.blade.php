@props(['title' => null, 'subtitle' => null, 'icon' => null])

<div>
  <h1 class="flex items-center gap-2 text-4xl font-bold text-base-content font-display {{ $subtitle ? '' : 'mb-6' }}">
  @if ($icon)
    <x-icon
      name="{{ $icon }}"
      class="inline-block w-8 h-8"
      />
  @endif
  {{ $title ?? $slot }}
  </h1>
  @if ($subtitle)
    <h2 class="text-base-content/60 text-sm mb-6">{{ $subtitle }}</h2>
  @endif
</div>