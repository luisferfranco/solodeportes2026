@props(['title' => null, 'subtitle' => null])

<div>
  <h1 class="text-2xl font-bold text-secondary uppercase tracking-wider {{ $subtitle ? '' : 'mb-6' }}">{{ $title ?? $slot }}</h1>
  @if ($subtitle)
    <h2 class="text-xl text-base-content mb-6">{{ $subtitle }}</h2>
  @endif
</div>