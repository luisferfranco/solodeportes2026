<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, viewport-fit=cover">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <meta name="csrf-token" content="{{ csrf_token() }}">

  <title>{{ isset($title) ? $title.' - '.config('app.name') : config('app.name') }}</title>

  @vite(['resources/css/app.css', 'resources/js/app.js'])

  {{-- EasyMDE --}}
  <link rel="stylesheet" href="https://unpkg.com/easymde/dist/easymde.min.css">
  <script src="https://unpkg.com/easymde/dist/easymde.min.js"></script>
  </head>
<body class="min-h-screen font-sans antialiased bg-base-200">

  {{-- NAVBAR mobile only --}}
  <x-nav sticky class="lg:hidden">
    <x-slot:brand>
      <img src="/img/solodeplogo.png" alt="Logo" class="w-12 h-12 rounded-full me-2">
    </x-slot:brand>
    <x-slot:actions>
      <label for="main-drawer" class="lg:hidden me-3">
      <x-icon name="o-bars-3" class="cursor-pointer" />
      </label>
    </x-slot:actions>
  </x-nav>

  <x-main>
    <x-slot:sidebar
      drawer="main-drawer"
      class="bg-base-100 lg:bg-base-100/75 border-r border-base-300 flex flex-col h-screen shadow-m rounded-l-xl"
      >

      <div class="flex w-full justify-center">
        <img src="/img/solodeplogo.png" alt="Logo" class="w-32 h-32 rounded-full">
      </div>

      @if($user = auth()->user())
        <div class="flex gap-1 px-3 items-start">
          <img
            src="{{ $user->avatarUrl }}"
            class="w-10 h-10 rounded-full inline-block me-2"
            >
          <div>
            <div class="font-bold">{{ $user->displayName }}</div>
            <p class="text-xs text-base-content/75">Saldo: <span class="font-bold font-mono">{{ Number::format($user->saldo,2) }}</span></p>
            <div class="flex gap-1 items-center">
              <x-button icon="lucide.power" class="btn-circle btn-accent btn-ghost btn-xs" no-wire-navigate link="/logout" />
              <x-button icon="lucide.settings" class="btn-circle btn-accent btn-ghost btn-xs" link="/profile" />
              <x-theme-toggle darkTheme="darkqn" />
            </div>
          </div>
        </div>
      @endif

      <livewire:aside-menu />
    </x-slot:sidebar>

    <x-slot:content>
      {{ $slot }}
    </x-slot:content>
  </x-main>

  <x-toast />
</body>
</html>
