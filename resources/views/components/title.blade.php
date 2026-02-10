@props(['title' => null])

<h1 class="text-2xl font-bold text-secondary uppercase tracking-wider mb-6">{{ $title ?? $slot }}</h1>